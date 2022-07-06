<?php

namespace Duplicator\Libs\DupArchive\Info;

use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;

class DupArchiveExpanderInfo
{
    public $archiveHandle = null;
    /** @var DupArchiveFileHeader */
    public $currentFileHeader   = null;
    public $destDirectory       = null;
    public $directoryWriteCount = 0;
    public $fileWriteCount      = 0;
    public $enableWrite         = false;

    /**
     * Get dest path
     *
     * @return string
     */
    public function getCurrentDestFilePath()
    {
        if ($this->destDirectory != null) {
            return "{$this->destDirectory}/{$this->currentFileHeader->relativePath}";
        } else {
            return null;
        }
    }
}
