<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\States;

/**
 * Dup archive create state
 */
class DupArchiveCreateState extends DupArchiveStateBase
{
    const DEFAULT_GLOB_SIZE = 1048576;

    public $basepathLength        = 0;
    public $currentDirectoryIndex = 0;
    public $currentFileIndex      = 0;
    public $globSize              = self::DEFAULT_GLOB_SIZE;
    public $newBasePath           = null;
    public $skippedFileCount      = 0;
    public $skippedDirectoryCount = 0;

    /**
     * Reset values
     *
     * @return void
     */
    public function reset()
    {
        parent::reset();
        $this->basepathLength        = 0;
        $this->currentDirectoryIndex = 0;
        $this->currentFileIndex      = 0;
        $this->globSize              = self::DEFAULT_GLOB_SIZE;
        $this->newBasePath           = null;
        $this->skippedFileCount      = 0;
        $this->skippedDirectoryCount = 0;
    }
}
