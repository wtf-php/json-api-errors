<?php

use Lukasoppermann\Httpstatus\Httpstatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorMiddleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

// How to start:
// php -S localhost:8080 examples/SingleExceptionMiddlewareExample.php
// GET http://localhost:8080/single

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();

$response = new Response();

$app->add(new JsonApiErrorMiddleware(new JsonApiErrorService(
    new JsonApiErrorFactory(false),
    new JsonApiErrorResponseFactory($response),
    new JsonApiErrorResponseSchema(),
    new Httpstatus()
)));

$app->get('/single', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    throw new Exception('Testing middleware for a single exception');
});

$app->run();
