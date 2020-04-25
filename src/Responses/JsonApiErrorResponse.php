<?php

namespace WtfPhp\JsonApiErrors\Responses;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use WtfPhp\JsonApiErrors\JsonApiBody;

/**
 * Class JsonApiErrorResponse
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiErrorResponse implements ResponseInterface
{
    private int $status = 0;
    private string $reasonPhrase = '';
    private string $protocolVersion = '';
    private array $headers = [];
    private ?StreamInterface $body = null;

    public function __construct()
    {
        $this->body = new JsonApiBody();
    }

    public function getStatusCode()
    {
        return $this->status;
    }

    public function withStatus($status, $reasonPhrase = '')
    {
        $this->status = $status;
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
