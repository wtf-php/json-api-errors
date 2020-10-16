<?php

use Lukasoppermann\Httpstatus\Httpstatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiMultipleErrorMiddleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$bag = new ThrowablesBag();

$app->add(new JsonApiMultipleErrorMiddleware(
    new JsonApiErrorService(
        new JsonApiErrorFactory(),
        new JsonApiErrorResponseFactory(),
        new JsonApiErrorResponseSchema(),
        new Httpstatus()
    ),
    $bag
));

$app->get('/index', function (ServerRequestInterface $request): ResponseInterface {
    /*
     $bag->add(new Exception('Testing middleware for multiple exceptions', 500));
     $bag->add(new Exception('Testing middleware for multiple exception 2'));
    */
     $response = new Response();
     return $response->withStatus(200);
});

$app->run();
