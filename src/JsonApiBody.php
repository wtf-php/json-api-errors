<?php

namespace WtfPhp\JsonApiErrors;

use Psr\Http\Message\StreamInterface;

/**
 * Class JsonApiBody
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiBody implements StreamInterface
{
    private string $content = '';

    /** @inheritDoc */
    public function __toString(): string
    {
        return $this->content;
    }

    /** @inheritDoc */
    public function close(): void
    {
        $this->content = '';
    }

    /** @inheritDoc */
    public function detach(): void
    {
        $this->content = '';
    }

    /** @inheritDoc */
    public function getSize(): int
    {
        return strlen($this->content);
    }

    /** @inheritDoc */
    public function tell()
    {
        return $this->getSize();
    }

    /** @inheritDoc */
    public function eof()
    {
        return false;
    }

    /** @inheritDoc */
    public function isSeekable()
    {
        return false;
    }

    /** @inheritDoc */
    public function seek($offset, $whence = SEEK_SET)
    {
    }

    /** @inheritDoc */
    public function rewind()
    {
    }

    /** @inheritDoc */
    public function isWritable()
    {
        return true;
    }

    /** @inheritDoc */
    public function write($string)
    {
        $this->content .= $string;
        return strlen($string);
    }

    /** @inheritDoc */
    public function isReadable()
    {
        return false;
    }

    /** @inheritDoc */
    public function read($length)
    {
    }

    /** @inheritDoc */
    public function getContents()
    {
        return $this->content;
    }

    /** @inheritDoc */
    public function getMetadata($key = null)
    {
    }
}
