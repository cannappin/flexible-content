<?php

/**
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils\HTTP;

use Duplicator\Libs\Snap\JsonSerialize\AbstractJsonSerializable;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;
use Requests;
use Requests_Response;
use Requests_Response_Headers;

class DynamicChunkRequests extends AbstractJsonSerializable
{
    /** @var int */
    const CHUNK_SIZE_MIN = 10240; // 10k in bytes
    /** @var int */
    const CHUNK_SIZE_MAX = 104857600; // 100MB in bytes
    /** @var float */
    const DEFAULT_CHUNK_TIME = 2; // seconds, can be a float number

    /** @var string original url request*/
    protected $url = '';
    /** @var string real download url */
    protected $downloadUrl = '';
    /** @var int */
    protected $chunkTime = self::DEFAULT_CHUNK_TIME;
    /** @var int */
    protected $offset = 0;
    /** @var int */
    protected $fullSize = -1;
    /** @var int */
    protected $lastSize = -1;
    /** @var int */
    protected $lastTime = -1;
    /** @var bool */
    protected $complete = false;

    /**
     * Class constructor
     *
     * @param string $url request URL
     */
    public function __construct($url)
    {
        if (strlen($this->url = (string) $url) == 0) {
            throw new Exception('URL can\'t be empty');
        }

        $this->downloadUrl = $this->url;
    }

    /**
     * Main interface for HTTP requests.
     *
     * This function wraps the Requests::request function of Wordpress application a dynamic range based on class settings
     *
     * @throws Requests_Exception On invalid URLs (`nonhttp`)
     *
     * @param array      $headers Extra headers to send with the request
     * @param array|null $data    Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
     * @param string     $type    HTTP request type (use Requests constants)
     * @param array      $options Options for the request (see description for more information)
     *
     * @return Requests_Response|bool returns xxx or false if no request was made,
     *                                false is not necessarily an error. Simply that the offset is out of range
     */
    public function request($headers = array(), $data = array(), $type = Requests::GET, $options = array())
    {
        if (($range = $this->getRequestRange()) === false) {
            $this->lastSize = -1;
            $this->lastTime = -1;
            $this->complete = true;
            return false;
        }

        $headers['Range'] = 'bytes=' . $range;
        \DUP_PRO_Log::trace('REQUEST HEADERS ' . SnapLog::v2str($headers));
        $startTime          = microtime(true);
        $options['timeout'] = ($this->chunkTime * 100); // make sure avoid the request timeout

        $response = Requests::request($this->downloadUrl, $headers, $data, $type, $options);

        if ($response->success !== true) {
            $this->lastSize = -1;
            $this->lastTime = -1;
            $this->complete = true;
            return $response;
        }

        $headers        = $response->headers->getAll();
        $this->lastTime = microtime(true) - $startTime;
        $this->lastSize = (int) self::getLastHeaderValue($response->headers, 'content-length', -1);
        if ($this->lastSize == -1) {
            /** @todo Implement a protocol extension system based on cloud type. */
            $this->lastSize = (int) self::getLastHeaderValue($response->headers, 'x-dropbox-content-length', -1);
        }
        \DUP_PRO_Log::trace('REMOTE RESPONSE CONTENT LEN ' . SnapLog::v2str($this->lastSize));

        if ($response->status_code == 200) {
            $this->fullSize = $this->lastSize;
            $this->offset   = $this->lastSize + 1;
            $this->complete = true;
            return $response;
        }

        $matches      = array();
        $contentRange = self::getLastHeaderValue($response->headers, 'content-range', '');
        \DUP_PRO_Log::trace('REMOTE RESPONSE CONTENT RANGE ' . SnapLog::v2str($contentRange) . "\n");

        if (
            $response->status_code != 206 ||
            preg_match('/bytes\s+(\d+)-(\d+)\/(\d+|\*)/', $contentRange, $matches) !== 1
        ) {
            $this->lastSize = -1;
            $this->complete = true;
            return $response;
        }

        $this->fullSize = ($matches[3] == '*' ? -1 : (int) $matches[3]);
        $this->offset   = ((int) $matches[2]) + 1;
        $this->complete = ($this->offset >= $this->fullSize);

        return $response;
    }

    /**
     * Reset current chunk donwload.
     *
     * @return void
     */
    public function reset()
    {
        $this->offset   = 0;
        $this->fullSize = -1;
        $this->lastSize = -1;
        $this->lastTime = -1;
        $this->complete = false;
    }

    /**
     * Return complete status
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * Calculate request range
     *
     * @return string|bool
     */
    protected function getRequestRange()
    {
        \DUP_PRO_Log::trace('LAST SIZE ' . SnapLog::v2str($this->lastSize));
        \DUP_PRO_Log::trace('LAST TIME ' . SnapLog::v2str($this->lastTime));

        if ($this->fullSize >= 0 && $this->offset >= $this->fullSize) {
            return false;
        }

        if ($this->lastSize <= 0 || $this->lastTime <= 0) {
            return ($this->offset . '-' . (self::CHUNK_SIZE_MIN - 1));
        }

        $size = SnapUtil::getIntBetween(
            floor($this->lastSize / $this->lastTime * $this->chunkTime),
            self::CHUNK_SIZE_MIN,
            self::CHUNK_SIZE_MAX
        );

        \DUP_PRO_Log::trace('NEW SIZE ' . SnapLog::v2str($size));
        if (($this->offset + $size) >= $this->fullSize) {
            return ($this->offset . '-');
        }

        return ($this->offset . '-' . ($this->offset + $size - 1));
    }

    /**
     * Function that returns the last value of a key in the header.
     * In case of redirect of the demand the values can be multiple therefore the last one makes reference to the last loaded URL
     *
     * @param Requests_Response_Headers $headers response headers
     * @param string                    $key     header key
     * @param mixed                     $default default value if header don't exists
     *
     * @return mixed
     */
    protected static function getLastHeaderValue(Requests_Response_Headers $headers, $key, $default = false)
    {
        if (($result = $headers->getValues($key)) === null) {
            return $default;
        }

        return end($result);
    }

    /**
     * Get the value of url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the value of fullSize
     *
     * @return int -1 if is unknown
     */
    public function getFullSize()
    {
        return $this->fullSize;
    }
}
