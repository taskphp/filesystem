<?php

namespace Task\Plugin\Filesystem;

use Symfony\Component\Finder\Finder as BaseFinder;
use Task\Plugin\Stream\ReadableInterface;
use Task\Plugin\Stream\WritableInterface;

class Finder extends BaseFinder implements ReadableInterface
{
    public function read()
    {
        return $this;
    }

    public function pipe(WritableInterface $to)
    {
        return $to->write($this->read());
    }
}
