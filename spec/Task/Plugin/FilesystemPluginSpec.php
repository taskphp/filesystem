<?php

namespace spec\Task\Plugin;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use Symfony\Component\Finder\Finder;

class FilesystemPluginSpec extends ObjectBehavior
{
    private $root;

    function let()
    {
        $this->root = vfsStream::setup('tmp');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Task\Plugin\FilesystemPlugin');
    }

    function it_should_open_a_file()
    {
        $path = $this->_touch('test');

        $file = $this->open($path);
        $file->shouldHaveType('Task\Plugin\Filesystem\File');
        $file->getPathname()->shouldReturn($path);
    }

    function it_should_touch_a_file()
    {
        $path = $this->_url('test');

        $file = $this->touch($path);
        $file->shouldHaveType('Task\Plugin\Filesystem\File');
        $file->getPathname()->shouldReturn($path);
    }

    function it_should_list_a_directory()
    {
        $path = vfsStream::url('tmp');

        $dir = $this->ls($path);
        $dir->shouldHaveType('Task\Plugin\Filesystem\FilesystemIterator');
        $dir->getPath()->shouldReturn($path);
    }

    function it_should_copy_file_to_dir()
    {
        $source = $this->_touch('source', 'foo');
        $dir = $this->_mkdir('test');
        $target = "$dir/source";

        $file = $this->copy($source, $dir);
        $file->shouldHaveType('Task\Plugin\Filesystem\File');
        $file->getPathname()->shouldReturn("$dir/source");

        expect($this->root->HasChild('test/source'))->toBe(true);
        expect(file_get_contents($target))->toBe('foo');
    }
        
    function it_should_copy_link()
    {
        $tmp = sys_get_temp_dir().'/task'.time();
        mkdir($tmp);

        $source = "$tmp/source";
        file_put_contents($source, 'foo');

        $link = "$tmp/link";
        symlink($source, $link);

        $target = "$tmp/target";

        $file = $this->copy($link, $target);
        $file->shouldHaveType('Task\Plugin\Filesystem\File');
        $file->getPathname()->shouldReturn($target);

        expect(readlink($target))->toBe($source);

        `rm -rf $tmp`;
    }

    function it_should_copy_file_to_file()
    {
        $source = $this->_touch('source', 'foo');
        $target = $this->_url('target');

        $file = $this->copy($source, $target);
        $file->shouldHaveType('Task\Plugin\Filesystem\File');
        $file->getPathname()->shouldReturn($target);

        expect($this->root->hasChild('target'))->toBe(true);
        expect(file_get_contents($target))->toBe('foo');
    }

    function it_should_throw_on_copy_dir_to_file()
    {
        $source = $this->_mkdir('source');
        $target = $this->_touch('target');

        $this->shouldThrow('LogicException')->duringCopy($source, $target);
    }

    function it_should_copy_dir_to_dir()
    {
        $this->root = vfsStream::setup('tmp', null, [
            'source' => [
                'foo' => [
                    'bar'
                ]
            ]
        ]);

        $source = $this->_url('source');
        $this->_touch('source/foo/bar', 'foo');
        $target = $this->_url('target');

        $dir = $this->copy($source, $target);
        $dir->shouldHaveType('Task\Plugin\Filesystem\FilesystemIterator');
        $dir->getPath()->shouldReturn($target);

        expect($this->root->getChild('target')->getType())->toBe(vfsStreamContent::TYPE_DIR);
        expect($this->root->getChild('target/foo')->getType())->toBe(vfsStreamContent::TYPE_DIR);
        expect(file_get_contents("$target/foo/bar"))->toBe('foo');
    }

    function it_should_throw_on_copy_nope()
    {
        $this->shouldThrow('Symfony\Component\Filesystem\Exception\FileNotFoundException')->duringCopy('nope', 'wow');
    }

    function it_should_mirror()
    {
        $source = $this->_mkdir('source');
        $this->_touch('source/foo');
        $this->_touch('source/bar');
        $target = $this->_url('target');

        $finder = new Finder;
        $finder->name('foo')->in($source);
        $this->mirror($source, $target, $finder);

        expect($this->root->hasChild('target/foo'))->toBe(true);
        expect($this->root->hasChild('target/bar'))->toBe(false);
    }

    private function _url($name)
    {
        return vfsStream::url('tmp')."/$name";
    }

    private function _touch($name, $content = null)
    {
        $path = $this->_url($name);
        file_put_contents($path, $content);
        return $path;
    }

    private function _mkdir($name)
    {
        $path = $this->_url($name);
        mkdir($path);
        return $path;
    }
}
