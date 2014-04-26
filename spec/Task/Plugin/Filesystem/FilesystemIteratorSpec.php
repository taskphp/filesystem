<?php

namespace spec\Task\Plugin\Filesystem;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Task\Plugin\Stream\WritableInterface;

class FilesystemIteratorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(sys_get_temp_dir());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Task\Plugin\Filesystem\FilesystemIterator');
    }

    function it_should_be_a_recursive_iterator()
    {
        $this->shouldHaveType('RecursiveIteratorIterator');
    }

    function it_should_be_readable()
    {
        $this->shouldHaveType('Task\Plugin\Stream\ReadableInterface');
    }

    function it_should_preserve_original_path()
    {
        $this->getPath()->shouldEqual(sys_get_temp_dir());
    }

    function it_should_read()
    {
        $this->read()->shouldEqual($this);
    }

    function it_should_pipe(WritableInterface $to)
    {
        $to->write($this)->willReturn($to);
        $this->pipe($to)->shouldReturn($to);
    }
}
