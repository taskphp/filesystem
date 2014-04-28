<?php

namespace Task\Plugin;

use Task\Plugin\PluginInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Task\Plugin\Filesystem\File;
use Task\Plugin\Filesystem\FilesystemIterator;
use Symfony\Component\Finder\Finder;

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
                return parent::copy($source, $target.DIRECTORY_SEPARATOR.basename($source), $override);
            } elseif (is_link($source)) {
                return $this->symlink(readlink($source), $target);
            } else {
                return parent::copy($source, $target, $override);
            }
        } elseif (is_dir($source)) {
            if (is_file($target)) {
                throw new \LogicException("Cannot copy directory to file");
            } else {
                return $this->mirror($source, $target);
            }
        }

        throw new FileNotFoundException("Could not copy $source to $target");
    }

    public function copyTree($baseDir, $target, Finder $finder)
    {
        foreach ($finder as $file) {
            if (!$file->isDir()) {
                $path = substr($file->getPathname(), strlen("$baseDir/"));
                $this->copy("$baseDir/$path", "$target/$path");
            }
        }
    }
}
