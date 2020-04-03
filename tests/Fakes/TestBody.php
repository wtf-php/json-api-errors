<?php

namespace WtfPhp\JsonApiErrors\Tests\Fakes;

use Psr\Http\Message\StreamInterface;

class TestBody implements StreamInterface
{
    private string $content = '';

    public function __toString()
    {
        return $this->content;
    }

    public function close() {}

    public function detach() {}

    public function getSize()
    {
        return strlen($this->content);
    }

    public function tell()
    {
        return $this->getSize();
    }

    public function eof()
    {
        return false;
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET) {}

    public function rewind() {}

    public function isWritable()
    {
        return true;
    }

    public function write($string)
    {
        $this->content .= $string;
        return strlen($string);
    }

    public function isReadable()
    {
        return false;
    }

    public function read($length) {}

    public function getContents()
    {
        return $this->content;
    }

    public function getMetadata($key = null) {}
}
