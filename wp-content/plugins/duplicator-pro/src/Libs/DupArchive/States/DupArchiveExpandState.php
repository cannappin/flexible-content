<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\States;

use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;

/**
 * Dup archive expand state
 */
abstract class DupArchiveExpandState extends DupArchiveStateBase
{
    const VALIDATION_NONE     = 0;
    const VALIDATION_STANDARD = 1;
    const VALIDATION_FULL     = 2;

    /** @var DupArchiveFileHeader */
    public $currentFileHeader        = null;
    public $validateOnly             = false;
    public $validatiOnType           = self::VALIDATION_STANDARD;
    public $fileWriteCount           = 0;
    public $directoryWriteCount      = 0;
    public $expectedFileCount        = -1;
    public $expectedDirectoryCount   = -1;
    public $filteredDirectories      = array();
    public $excludedDirWithoutChilds = array();
    public $filteredFiles            = array();
    /** @var string[] relative path list to inclue files, overwrite filters */
    public $includedFiles = array();
    /** @var string[] relativePath => fullNewPath */
    public $fileRenames           = array();
    public $directoryModeOverride = -1;
    public $fileModeOverride      = -1;
    public $lastHeaderOffset      = -1;

    /**
     * Class constructor
     *
     * @param DupArchiveHeader $archiveHeader archive header
     */
    public function __construct(DupArchiveHeader $archiveHeader)
    {
        parent::__construct($archiveHeader);
    }

    /**
     * Reset values
     *
     * @return void
     */
    public function reset()
    {
        parent::reset();
        $this->currentFileHeader        = null;
        $this->validateOnly             = false;
        $this->validatiOnType           = self::VALIDATION_STANDARD;
        $this->fileWriteCount           = 0;
        $this->directoryWriteCount      = 0;
        $this->expectedFileCount        = -1;
        $this->expectedDirectoryCount   = -1;
        $this->filteredDirectories      = array();
        $this->excludedDirWithoutChilds = array();
        $this->filteredFiles            = array();
        $this->includedFiles            = array();
        $this->fileRenames              = array();
        $this->directoryModeOverride    = -1;
        $this->fileModeOverride         = -1;
        $this->lastHeaderOffset         = -1;
    }

    /**
     * Reset state for file
     *
     * @return void
     */
    public function resetForFile()
    {
        $this->currentFileHeader = null;
        $this->currentFileOffset = 0;
    }
}
