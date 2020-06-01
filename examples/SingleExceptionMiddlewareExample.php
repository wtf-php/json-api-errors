<?php

use Lukasoppermann\Httpstatus\Httpstatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorMiddleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

require '../vendor/autoload.php';

$app = AppFactory::create();

$app->add(new JsonApiErrorMiddleware(new JsonApiErrorService(
    new JsonApiErrorFactory(),
    new JsonApiErrorResponseFactory(),
    new JsonApiErrorResponseSchema(),
    new Httpstatus()
)));

$app->get('/index', function (ServerRequestInterface $request): ResponseInterface {
    throw new Exception('Testing middleware for a single exception');
});

$app->run();
