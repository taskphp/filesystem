<?php

namespace Task\Plugin\Filesystem;

use Task\Plugin\Stream;

class File extends \SplFileObject implements Stream\ReadableInterface, Stream\WritableInterface
{
    public function __construct($filename, $mode = 'r+')
    {
        try {
            parent::__construct($filename, $mode);
        } catch (\RuntimeException $ex) {
        }
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
        if ($data instanceof File) {
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

    public function pipe(Stream\WritableInterface $to)
    {
        return $to->write($this->read());
    }
}
