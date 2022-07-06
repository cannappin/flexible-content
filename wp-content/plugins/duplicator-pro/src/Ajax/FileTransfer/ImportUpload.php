<?php

/**
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax\FileTransfer;

use DUP_PRO_Log;
use DUP_PRO_Package_Importer;
use DUP_PRO_U;
use Duplicator\Libs\Snap\JsonSerialize\JsonSerialize;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;
use Requests;
use Duplicator\Utils\HTTP\DynamicChunkRequests;

class ImportUpload
{
    const P2P_TIMEOUT = 5; // seconds, can be a float number

    const MODE_UPLOAD_LOCAL    = 'upload'; // Upload archive from local PC
    const MODE_DOWNLOAD_REMOTE = 'remote'; // Download archive from remote URL
    const MODE_UPLOADED        = 'uploaded'; // Archive is already uploaded

    const STATUS_CHUNKING = 'chunking';
    const STATUS_COMPLETE = 'complete';

    /** @var string */
    protected $mode = '';
    /** @var string*/
    protected $status = self::STATUS_CHUNKING;
    /** @var bool */
    protected $isImportable = false;
    /** @var string */
    protected $archivePath = '';
    /** @var string  */
    protected $installerPageLink = '';
    /** @var string  */
    protected $htmlDetails = '';
    /** @var string  */
    protected $created = '';
    /** @var string  */
    protected $invalidMessage = '';
    /** @var int */
    protected $archiveSize = -1;
    /** @var null|DynamicChunkRequests */
    protected $remoteChunk = null;

    /**
     * Class constructor
     *
     * @param string $mode        upload mode
     * @param string $archivePath archive path, use in mode uploaded
     */
    public function __construct($mode, $archivePath = '')
    {
        switch ($mode) {
            case self::MODE_UPLOAD_LOCAL:
            case self::MODE_DOWNLOAD_REMOTE:
                break;
            case self::MODE_UPLOADED:
                if (strlen($archivePath) == 0 || !is_file($archivePath)) {
                    throw new Exception('Invalid archive');
                }
                $this->archivePath = $archivePath;
                break;
            default:
                throw new Exception('Invalid transfer mode');
        }
        $this->mode = $mode;
    }

    /**
     * Exec upload and return result
     *
     * @return array
     */
    public function exec()
    {
        if (!file_exists(DUPLICATOR_PRO_PATH_IMPORTS)) {
            SnapIO::mkdir(DUPLICATOR_PRO_PATH_IMPORTS, 0755, true);
        }

        switch ($this->mode) {
            case self::MODE_UPLOAD_LOCAL:
                $this->uploadLocal();
                break;
            case self::MODE_DOWNLOAD_REMOTE:
                $this->remoteDownload();
                break;
            case self::MODE_UPLOADED:
                $this->setCompleteData();
                break;
        }

        return JsonSerialize::valueToJsonData($this, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);
    }

    /**
     * Upload in local mode
     *
     * @return void
     */
    protected function uploadLocal()
    {
        $archiveName = isset($_FILES["file"]["name"]) ? sanitize_text_field($_FILES["file"]["name"]) : null;
        if (!preg_match(DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN, $archiveName)) {
            throw new Exception(__("Invalid archive file name. Please use the valid archive file!", 'duplicator-pro'));
        }
        $archiveNameTemp = isset($_FILES["file"]["tmp_name"]) ? sanitize_text_field($_FILES["file"]["tmp_name"]) : null;

        $currentChunk = filter_input(INPUT_POST, 'chunk', FILTER_VALIDATE_INT, array('options' => array('default' => false)));
        $numChunks    = filter_input(INPUT_POST, 'chunks', FILTER_VALIDATE_INT, array('options' => array('default' => false)));

        $this->archivePath = DUPLICATOR_PRO_PATH_IMPORTS . '/' . $archiveName;

        if ($numChunks !== false) {
            //CHUNK MODE
            $archivePart = $this->getArchivePart();

            // Clean last upload part leaved as it is (The situation in which user navigate to another url while uploading archive file path)
            if ($currentChunk === 0 && file_exists($archivePart)) {
                @unlink($archivePart);
            }

            SnapIO::appendFileToFile($archiveNameTemp, $archivePart);

            if ($currentChunk == ($numChunks - 1)) {
                if (SnapIO::rename($archivePart, $this->archivePath, true) === false) {
                    throw new Exception('Can\'t rename file part to file');
                }
                $this->setCompleteData();
            } else {
                $this->status = self::STATUS_CHUNKING;
            }
        } else {
            // DIRECT MODE
            if (move_uploaded_file($archiveNameTemp, $this->archivePath) === false) {
                throw new Exception(DUP_PRO_U::esc_html__('Can\'t rename file part to file'));
            }
            $this->setCompleteData();
        }
    }

    /**
     * Download archive from remote URL
     *
     * @return void
     */
    protected function remoteDownload()
    {
        $remoteURL = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL, array('options' => array('default' => false)));
        if ($remoteURL == false) {
            throw new Exception('Remove URL must be a valid URL');
        }
        $remoteURL = self::filterRealDownloadUrl($remoteURL);
        $parseUrl  = SnapURL::parseUrl($remoteURL);

        $archiveName = basename($parseUrl['path']);
        if (!preg_match(DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN, $archiveName)) {
            throw new Exception(__("Invalid archive file name. Please use the valid archive file!", 'duplicator-pro'));
        }
        $this->archivePath = DUPLICATOR_PRO_PATH_IMPORTS . '/' . $archiveName;
        $archivePart       = $this->getArchivePart();

        $restoreDownload = (isset($_POST['restoreDownload']) ? SnapUtil::sanitizeNSCharsNewline($_POST['restoreDownload']) : '');
        if (strlen($restoreDownload) > 0) {
            $restoreDownload   = stripslashes($restoreDownload);
            $this->remoteChunk = JsonSerialize::unserializeToObj($restoreDownload, 'Duplicator\\Utils\\HTTP\\DynamicChunkRequests');

            if (!file_exists($archivePart)) {
                throw new Exception('Can\t resume the download, archive part file don\'t exists');
            }

            if ($this->remoteChunk->getUrl() !== $remoteURL) {
                throw new Exception('Input params not valid');
            }

            // param validation check
            if (!SnapIO::isChildPath($this->archivePath, DUPLICATOR_PRO_PATH_IMPORTS, false, false)) {
                throw new Exception('Invalid params');
            }
        } else {
            if (file_exists($archivePart)) {
                unlink($archivePart);
            }
            $this->remoteChunk = new DynamicChunkRequests($remoteURL);
        }

        $startTime = microtime(true);
        do {
            $tmpFile  = tempnam(DUPLICATOR_PRO_PATH_IMPORTS, 'tmp_p2p_part_');
            $response = $this->remoteChunk->request(
                array(),
                array(),
                Requests::GET,
                array(
                    'filename' => $tmpFile,
                    'verify' => false,
                    'verifyname' => false
                )
            );

            if ($response->success == false) {
                throw new Exception("Remote URL request on " . $remoteURL . " failed");
            }

            SnapIO::appendFileToFile($tmpFile, $archivePart);

            $deltaTime = microtime(true) - $startTime;
        } while (!$this->remoteChunk->isComplete() && $deltaTime < self::P2P_TIMEOUT);

        if ($this->remoteChunk->isComplete()) {
            if (SnapIO::rename($archivePart, $this->archivePath, true) === false) {
                throw new Exception('Can\'t rename file part to file');
            }
            $this->setCompleteData();
        } else {
            $this->status = self::STATUS_CHUNKING;
        }
    }

    /**
     * This function processes the input URL and modifies it if it refers to a known cloud.
     *
     * @param string $url input URL
     *
     * @return string
     */
    protected function filterRealDownloadUrl($url)
    {
        $parseUrl = SnapURL::parseUrl($url);
        if (SnapURL::wwwRemove($parseUrl['host']) === 'dropbox.com') {
            parse_str($parseUrl['query'], $queryVals);
            if (isset($queryVals['dl'])) {
                $queryVals['dl']   = 1;
                $parseUrl['query'] = http_build_query($queryVals);
                return SnapURL::buildUrl($parseUrl);
            }
        }
        return $url;
    }

    /**
     * Get archvie part full path
     *
     * @return string
     */
    protected function getArchivePart()
    {
        return $this->archivePath . '.part';
    }

    /**
     * Set completa package upload data
     *
     * @return void
     */
    public function setCompleteData()
    {
        $this->status      = self::STATUS_COMPLETE;
        $this->remoteChunk = null;

        try {
            $importObj = new DUP_PRO_Package_Importer($this->archivePath);
            $importObj->cleanFolder();

            $this->isImportable      = $importObj->isImportable();
            $this->installerPageLink = $importObj->getInstallerPageLink();
            $this->htmlDetails       = $importObj->getHtmlDetails(false);
            $this->created           = $importObj->getCreated();
            if (($this->archiveSize = filesize($this->archivePath)) === false) {
                $this->archiveSize = -1;
            }
        } catch (Exception $e) {
            $this->isImportable      = false;
            $this->installerPageLink = '';
            $this->htmlDetails       = sprintf(DUP_PRO_U::esc_html__('Problem on import, message: %s'), $e->getMessage());
            $this->created           =  '';
            $this->invalidMessage    = $e->getMessage();
        }
    }
}
