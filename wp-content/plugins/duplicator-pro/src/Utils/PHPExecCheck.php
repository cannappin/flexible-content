<?php

/**
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils;

use Error;
use Exception;
use Requests;
use Requests_Auth_Basic;
use Requests_Response;

/**
 * PHP check utility
 */
class PHPExecCheck
{
    /** @var string */
    protected $dir = '';
    /** @var string */
    protected $url = '';
    /** @var string */
    protected $phpTestFile = '';
    /** @var null|Requests_Response */
    protected $lastResponse = null;

    const PHP_OK               = 1;
    const PHP_FAIL_FILE_CREATE = -1;
    const PHP_REQUEST_FAIL     = -2;
    const PHP_RESULT_FAIL      = -3;

    const TEST_FILE_PREFIX  = 'dup_php_test_';
    const TEST_FILE_CONTENT = <<<TEST
<?php echo "abcde";
TEST;


    /**
     * Class contructor
     *
     * @param string $dir dir to check
     * @param string $url related URL dir
     */
    public function __construct($dir, $url)
    {
        if (!is_dir($dir)) {
            throw new Exception('Dir ' . $dir . ' must be a directory');
        }
        $this->dir = trailingslashit($dir);
        $this->url = trailingslashit($url);
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        $this->removeTestFile();
    }

    /**
     * Check if PHP is executable in $dir
     *
     * @return int return PHP check result status (see constants)
     */
    public function check()
    {
        try {
            $this->lastResponse = null;

            if ($this->createTestFile() == false) {
                return self::PHP_FAIL_FILE_CREATE;
            }

            $options = array(
                //'max_bytes' => 250,
                'verify' => false,
                'verifyname' => false
            );

            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $options['auth'] = new Requests_Auth_Basic(array(
                    $_SERVER['PHP_AUTH_USER'],
                    (isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '')
                ));
            }

            $testUrl            = $this->url . basename($this->phpTestFile);
            $this->lastResponse = $response = Requests::get(
                $testUrl,
                array(),
                $options
            );

            $this->removeTestFile();

            if ($response->success !== true || $response->status_code !== 200) {
                return self::PHP_REQUEST_FAIL;
            }

            if (strcmp($response->body, 'abcde') !== 0) {
                return self::PHP_RESULT_FAIL;
            }
        } catch (Exception $e) {
            $this->removeTestFile();
            return self::PHP_REQUEST_FAIL;
        } catch (Error $e) {
            $this->removeTestFile();
            return self::PHP_REQUEST_FAIL;
        }

        return self::PHP_OK;
    }

    /**
     * Create test file, removes the old one if it already exists.
     *
     * @return bool Returns true on success or false on failure.
     */
    protected function createTestFile()
    {
        $this->removeTestFile();

        $tempfile = tempnam($this->dir, self::TEST_FILE_PREFIX);
        unlink($tempfile); // remove temp file to recreate it with php extension
        $this->phpTestFile = $tempfile . '.php';
        return (file_put_contents($this->phpTestFile, self::TEST_FILE_CONTENT) !== false);
    }

    /**
     * Remove test file if exists
     *
     * @return bool Returns true on success or false on failure.
     */
    protected function removeTestFile()
    {
        $result = true;
        if (strlen($this->phpTestFile) == 0) {
            return $result;
        }

        if (file_exists($this->phpTestFile)) {
            $result = unlink($this->phpTestFile);
        }

        $this->phpTestFile = '';
        return $result;
    }

    /**
     * Get responde of last check
     *
     * @return null|Requests_Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
