<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Lukasoppermann\Httpstatus\Httpstatus;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorPSR15Middleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestRequest;

abstract class BaseMiddlewareTest extends TestCase
{
    /**
     * @param string $expectedDataFile
     * @param ResponseInterface $response
     */
    protected function assertExpectedWithResponse(string $expectedDataFile, ResponseInterface $response)
    {
        $expectedResult = $this->decodeJsonFile(__DIR__ . '/expectations/' . $expectedDataFile);

        // Needed as the body is a stream and the cursor needs to be set back to the start
        $response->getBody()->rewind();
        $actualResult = json_decode($response->getBody()->getContents(), true);

        // Do they have the same amount of errors?
        $expectedErrors = $expectedResult['errors'];
        $actualErrors = $actualResult['errors'];
        $this->assertCount(count($expectedErrors), $actualErrors);

        // Does every error contain the same infos?
        foreach ($expectedErrors as $expectedErrorKey => $expectedErrorValues) {
            $actualErrorValues = $actualErrors[$expectedErrorKey];

            $this->assertCount(count($expectedErrorValues), $actualErrorValues);

            foreach ($expectedErrorValues as $expectedErrorValueKey => $expectedErrorValue) {
                // Special handling for `meta` as we don't want to compare stack-traces.
                if (is_array($expectedErrorValue) && $expectedErrorValueKey === 'meta') {
                    foreach ($expectedErrorValue as $key => $value) {
                        if ($key === 'trace') {
                            $this->assertArrayHasKey('trace', $expectedErrorValue);
                        } elseif ($key === 'file') {
                            $this->assertStringEndsWith($value, $actualErrorValues[$expectedErrorValueKey][$key]);
                        } else {
                            $this->assertEquals($value, $actualErrorValues[$expectedErrorValueKey][$key]);
                        }
                    }
                } else {
                    $this->assertEquals(
                        $expectedErrorValue,
                        $actualErrorValues[$expectedErrorValueKey]
                    );
                }
            }
        }
    }

    /**
     * @param string $path
     * @return array
     */
    protected function decodeJsonFile(string $path): array
    {
        $content = file_get_contents($path);
        return json_decode($content, true);
    }

    /**
     * @param bool $debugMode
     */
    protected function setUpWithMode(bool $debugMode = false): void
    {
        $this->request = new TestRequest();
        $this->responseFactory = new JsonApiErrorResponseFactory(new Response());
        $this->jsonApiErrorFactory = new JsonApiErrorFactory($debugMode);
        $this->jsonApiErrorResponseSchema = new JsonApiErrorResponseSchema();
        $this->httpStatusHelper = new Httpstatus();
        $this->jsonApiErrorService = new JsonApiErrorService(
            $this->jsonApiErrorFactory,
            $this->responseFactory,
            $this->jsonApiErrorResponseSchema,
            $this->httpStatusHelper
        );
        $this->bag = new ThrowablesBag();
        $this->middleware = new JsonApiErrorPSR15Middleware($this->jsonApiErrorService, $this->bag);
    }
}
