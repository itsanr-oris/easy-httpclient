<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient\Tests\Middleware;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ConnectException;
use Foris\Easy\HttpClient\Middleware\RetryMiddleware;
use Foris\Easy\HttpClient\Middleware\MiddlewareInterface;
use Foris\Easy\HttpClient\Test\HttpClientMiddlewareTestCase;

/**
 * Class RetryMiddlewareTest
 */
class RetryMiddlewareTest extends HttpClientMiddlewareTestCase
{
    /**
     * @var int
     */
    protected $maxRetries;

    /**
     * @var int
     */
    protected $retryDelay;

    /**
     * @return MiddlewareInterface
     */
    public function middleware()
    {
        $this->maxRetries = 1;
        $this->retryDelay = 100;
        return new RetryMiddleware(['max_retries' => $this->maxRetries, 'retry_delay' => $this->retryDelay]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSuccessRequest()
    {
        $this->appendResponse();
        $response = $this->client()->request('GET', '/');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRetryServerException()
    {
        $this->appendResponse(500)->appendResponse();
        $response = $this->client()->request('GET', '/');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRetryConnectException()
    {
        $this->appendException(new ConnectException('connect error', new Request('GET', '/')));
        $this->appendResponse();

        $response = $this->client()->request('GET', '/');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRetry500Response()
    {
        $this->appendResponse(500)->appendResponse();
        $response = $this->client()->request('GET', '/', ['http_errors' => false]);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRetryOutOfLimit()
    {
        $this->appendResponse(500)->appendResponse(501)->appendResponse();
        $response = $this->client()->request('GET', '/', ['http_errors' => false]);
        $this->assertSame(501, $response->getStatusCode());
    }
}
