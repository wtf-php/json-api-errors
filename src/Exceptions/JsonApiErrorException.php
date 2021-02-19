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
    protected string $detail = '';
    protected string $status = '500';
    protected string $id = '';
    protected array $meta = [];
    protected string $aboutLink = '';

    /**
     * JsonApiErrorException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param string $detail
     * @param string $status
     * @param string $id
     * @param array $meta
     * @param string $aboutLink
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null,
        string $detail = '',
        string $status = '500',
        string $id = '',
        array $meta = [],
        string $aboutLink = ''
    ) {
        parent::__construct($message, $code, $previous);

        $this->detail = $detail;
        $this->status = $status;
        $this->id = $id;
        $this->meta = $meta;
        $this->aboutLink = $aboutLink;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
