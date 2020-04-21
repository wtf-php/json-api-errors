<?php

namespace WtfPhp\JsonApiErrors\Models;

/**
 * Class JsonApiError
 * @package WtfPhp\JsonApiErrors\Models
 */
class JsonApiError
{
    public string $id;
    public string $code;
    public int $status;
    public string $title;
    public string $detail;
    public array $links;
    public array $source;
    public array $meta;
}
