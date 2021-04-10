<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient\Tests\Middleware;

use Foris\Easy\HttpClient\Tests\TestCase;

/**
 * Class FixJsonOptionsMiddleware
 */
class FixJsonOptionsMiddlewareTest extends TestCase
{
    /**
     * Test fix options with empty json data.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testFixOptionsWithEmptyJsonData()
    {
        $this->mockResponse();
        $this->httpClient()->postJson('http://localhost/demo', []);

        $request = $this->lastRequest();
        $this->assertEquals(\GuzzleHttp\json_encode([], JSON_FORCE_OBJECT), $request->getBody());
    }

    /**
     * Test fix options with normal json data.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testFixOptionsWithNormalJsonData()
    {
        $this->mockResponse();
        $this->httpClient()->postJson('http://localhost/demo', ['key' => 'value']);

        $request = $this->lastRequest();
        $this->assertEquals(\GuzzleHttp\json_encode(['key' => 'value'], JSON_UNESCAPED_UNICODE), $request->getBody());
    }
}
