<?php

use Lukasoppermann\Httpstatus\Httpstatus;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiMultipleErrorMiddleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

// How to start:
// php -S localhost:8080 examples/MultipleExceptionMiddlewareExample.php
// GET http://localhost:8080/multiple

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$bag = new ThrowablesBag();

$app->add(
    new JsonApiMultipleErrorMiddleware(
        new JsonApiErrorService(
            new JsonApiErrorFactory(false),
            new JsonApiErrorResponseFactory(),
            new JsonApiErrorResponseSchema(),
            new Httpstatus()
        ),
        $bag
    )
);

$app->get('/multiple', function () use ($bag): ResponseInterface {
     $bag->add(new Exception('Testing middleware for multiple exceptions', 500));
     $bag->add(new Exception('Testing middleware for multiple exception 2', 400));

     return new Response();
});

$app->run();
