<?php

namespace Task\Plugin\Filesystem;

use Task\Plugin\Stream\ReadableInterface;
use Task\Plugin\Stream\WritableInterface;

class FilesystemIterator extends \RecursiveIteratorIterator implements ReadableInterface
{
    public function __construct(
        $path,
        $directoryOptions = \FilesystemIterator::SKIP_DOTS,
        $iteratorOptions = null
    ) {
        parent::__construct(
            new \RecursiveDirectoryIterator(
                $path,
                $directoryOptions
            ),
            $iteratorOptions
        );

        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function read()
    {
        return $this;
    }

    public function pipe(WritableInterface $to)
    {
        return $to->write($this->read());
    }
}
