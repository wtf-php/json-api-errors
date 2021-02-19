<?php

namespace WtfPhp\JsonApiErrors\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

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
                $this->assertEquals(
                    $expectedErrorValues[$expectedErrorValueKey],
                    $actualErrorValues[$expectedErrorValueKey]
                );
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
}
