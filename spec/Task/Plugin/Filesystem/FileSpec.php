<?php

namespace spec\Task\Plugin\Filesystem;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Task\Plugin\Filesystem\File;
use org\bovigo\vfs\vfsStream;
use Task\Plugin\Stream\WritableInterface;

class FileSpec extends ObjectBehavior
{
    private $root;
    private $path;

    function let()
    {
        $this->root = vfsStream::setup('tmp');
        $this->path = vfsStream::url('tmp').'/test';
        $this->beConstructedWith($this->path);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Task\Plugin\Filesystem\File');
    }

    function it_should_be_an_spl_file_object()
    {
        $this->shouldHaveType('SplFileObject');
    }

    function it_should_be_readable()
    {
        $this->shouldHaveType('Task\Plugin\Stream\ReadableInterface');
    }

    function it_should_be_writable()
    {
        $this->shouldHaveType('Task\Plugin\Stream\WritableInterface');
    }

    function it_should_read_content()
    {
        file_put_contents($this->path, 'foo');
        $this->read()->shouldReturn('foo');
    }

    function it_should_write_content()
    {
        $this->write('foo')->shouldReturn($this);
        expect(file_get_contents($this->path))->toBe('foo');
    }

    function it_should_write_new_file_content()
    {
        $src = vfsStream::url('tmp').'/src';

        $src = new File($src);
        $this->write('bar');

        $this->read()->shouldReturn('bar');
    }

    function it_should_overwrite_file_content()
    {
        $src = vfsStream::url('tmp').'/src';
        file_put_contents($src, 'foo');

        $src = new File($src);
        $this->write('bar');

        $this->read()->shouldReturn('bar');
    }

    function it_should_append_content()
    {
        file_put_contents($this->path, 'foo');
        $this->append('bar')->shouldReturn($this);
        expect(file_get_contents($this->path))->toBe('foobar');
    }

    function it_should_pipe(WritableInterface $to)
    {
        file_put_contents($this->path, 'foo');
        $to->write('foo')->willReturn($to);
        $this->pipe($to)->shouldReturn($to);
    }
}
