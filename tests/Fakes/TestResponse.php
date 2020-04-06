<?php

namespace WtfPhp\JsonApiErrors\Tests\Fakes;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class TestResponse implements ResponseInterface
{
    private int $statusCode;

    private string $reasonPhrase;

    private string $protocolVersion;

    private array $headers;

    private StreamInterface $body;

    public function __construct()
    {
        $this->body = new TestBody();
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase;

        return $this;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version)
    {
        $this->protocolVersion = $version;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return array_key_exists($name, $this->headers);
    }

    public function getHeader($name)
    {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = [$value];
        return $this;
    }

    public function withAddedHeader($name, $value)
    {
        if (!array_key_exists($name, $this->headers)) {
            $this->headers[$name] = [];
        }

        $this->headers[$name][] = $value;
        return $this;
    }

    public function withoutHeader($name)
    {
        unlink($this->headers[$name]);
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        $this->body = $body;
        return $this;
    }
}
