<?php

namespace WtfPhp\JsonApiErrors\Exceptions;

use Exception;
use Throwable;

/**
 * Class JsonApiErrorException
 * @package WtfPhp\JsonApiErrors\Exceptions
 */
class JsonApiErrorException extends Exception
{
    protected string $id = '';
    protected string $statusCode = '500';
    protected array $meta = [];
    protected string $aboutLink = '';

    /**
     * JsonApiErrorException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param string $id
     * @param string $statusCode
     * @param array $meta
     * @param string $aboutLink
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null,
        string $id = '',
        string $statusCode = '500',
        array $meta = [],
        string $aboutLink = ''
    ) {
        parent::__construct($message, $code, $previous);
        $this->id = $id;
        $this->code = $code;
        $this->meta = $meta;
        $this->statusCode = $statusCode;
        $this->aboutLink = $aboutLink;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return string
     */
    public function getAboutLink(): string
    {
        return $this->aboutLink;
    }
}
