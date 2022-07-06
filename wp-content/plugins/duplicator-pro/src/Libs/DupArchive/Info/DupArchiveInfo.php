<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\Info;

class DupArchiveInfo
{
    public $archiveHeader;
    public $fileHeaders;
    public $directoryHeaders;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->fileHeaders      = array();
        $this->directoryHeaders = array();
    }
}
