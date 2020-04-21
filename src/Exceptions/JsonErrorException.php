<?php

namespace WtfPhp\JsonApiErrors\Exceptions;

use Exception;
use Throwable;

/**
 * Class JsonErrorException
 * @package WtfPhp\JsonApiErrors\Exceptions
 */
class JsonErrorException extends Exception
{
    protected string $id;
    protected int $statusCode;
    protected array $meta;

    /**
     * JsonErrorException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param string $id
     * @param int $statusCode
     * @param array $meta
     */
    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null,
        string $id = '',
        int $statusCode = 500,
        array $meta = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->id = $id;
        $this->code = $code;
        $this->meta = $meta;
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
