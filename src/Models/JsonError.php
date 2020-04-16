<?php

namespace WtfPhp\JsonApiErrors\Models;

/**
 * Class JsonError
 * @package WtfPhp\JsonApiErrors\Models
 */
class JsonError
{
    public string $id;
    public string $code;
    public int $status;
    public string $title;
    public string $detail;
    public array $links;
    public array $source;
    public $meta;
}
