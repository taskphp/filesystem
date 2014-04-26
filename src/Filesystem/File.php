<?php

namespace Task\Plugin\Filesystem;

use Task\Plugin\Stream\WritableInterface;
use Task\Plugin\Stream\ReadableInterface;

class File extends \SplFileObject implements ReadableInterface, WritableInterface
{
    public function __construct($filename, $mode = 'r+')
    {
        touch($filename);
        parent::__construct($filename, $mode);
    }

    public function read()
    {
        $this->rewind();

        $content = '';
        while (!$this->eof()) {
            $content .= $this->fgets();
        }

        return $content;
    }

    public function write($data)
    {
        if ($data instanceof ReadableInterface) {
            $data = $data->read();
        }

        $this->ftruncate(0);
        $this->fwrite($data);
        return $this;
    }

    public function append($content)
    {
        while (!$this->eof()) {
            # why doesn't next() work here?
            $this->current();
        }

        $this->fwrite($content);
        return $this;
    }

    public function pipe(WritableInterface $to)
    {
        return $to->write($this->read());
    }
}
