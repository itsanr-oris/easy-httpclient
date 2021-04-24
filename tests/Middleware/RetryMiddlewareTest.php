<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient\Tests\Middleware;

use Foris\Easy\HttpClient\Tests\TestCase;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;

/**
 * Class RetryMiddlewareTest
 */
class RetryMiddlewareTest extends TestCase
{
    /**
     * When a connection error occurs, try to resend the request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRetryWithConnectionException()
    {
        $this->mockHttpException(
            new ConnectException('Connection exception', new Request('GET', 'http://localhost/demo'))
        );

        $this->mockResponse(['data' => 'Right response.']);
        $response = $this->httpClient()->get('http://localhost/demo');
        $this->assertCount(2, $this->historyRequest());
        $this->assertEquals('Right response.', $response['data']);
    }

    /**
     * When a server error occurs, try to resend the request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRetryWithServerException()
    {
        $this->mockHttpException(
            new ServerException('Server exception', new Request('GET', 'http://localhost/demo'))
        );

        $this->mockResponse(['data' => 'Right response.']);
        $response = $this->httpClient()->get('http://localhost/demo');
        $this->assertCount(2, $this->historyRequest());
        $this->assertEquals('Right response.', $response['data']);
    }

    /**
     * When response with 50x status code, try to resend the request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRetryWith50xHttpCode()
    {
        $this->mockResponse([], 500)->mockResponse([], 500);
        $response = $this->httpClient()->httpErrors(false)->castResponse(false)->get('http://localhost/demo');
        $this->assertCount(2, $this->historyRequest());
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test maximum number of retries reached.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testMaxRetryTimesReached()
    {
        $this->mockResponse(['data' => 'The 1st response.'], 500)
            ->mockResponse(['data' => 'The 2nd response.'], 500)
            ->mockResponse(['data' => 'The 3rd response.'], 200);

        $response = $this->httpClient()->httpErrors(false)->get('http://localhost/demo');
        $this->assertCount(2, $this->historyRequest());
        $this->assertEquals('The 2nd response.', $response['data']);
    }
}
