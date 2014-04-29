<?php

namespace Task\Plugin;

use Task\Plugin\PluginInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Task\Plugin\Filesystem\File;
use Task\Plugin\Filesystem\FilesystemIterator;

class FilesystemPlugin extends Filesystem implements PluginInterface
{
    public function open($filename, $mode = 'r+')
    {
        return new File($filename, $mode);
    }

    public function touch($filename, $time = null, $atime = null)
    {
        parent::touch($filename, $time, $atime);
        return $this->open($filename);
    }

    public function ls($dir)
    {
        return new FilesystemIterator($dir);
    }

    public function copy($source, $target, $override = false)
    {
        $target = rtrim($target, '/');
        $source = rtrim($source, '/');

        if (is_file($source)) {
            if (is_dir($target)) {
                $target = $target.DIRECTORY_SEPARATOR.basename($source);
                parent::copy($source, $target, $override);
                return $this->open($target);
            } elseif (is_link($source)) {
                $this->symlink(readlink($source), $target);
                return $this->open($target);
            } else {
                parent::copy($source, $target, $override);
                return $this->open($target);
            }
        } elseif (is_dir($source)) {
            if (is_file($target)) {
                throw new \LogicException("Cannot copy directory to file");
            } else {
                $this->mirror($source, $target, null, ['override' => $override]);
                return $this->ls($target);
            }
        }

        throw new FileNotFoundException("Could not copy $source to $target");
    }

    public function mirror($originDir, $targetDir, \Traversable $iterator = null, $options = [])
    {
        parent::mirror($originDir, $targetDir, $iterator, $options);
        return $this->ls($targetDir);
    }
}
