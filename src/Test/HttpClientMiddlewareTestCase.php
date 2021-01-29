<?php

namespace Foris\Easy\HttpClient\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Foris\Easy\HttpClient\Middleware\MiddlewareInterface;

/**
 * Class HttpClientMiddlewareTestCase
 */
abstract class HttpClientMiddlewareTestCase extends TestCase
{
    /**
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();
        $this->mockHandler = new MockHandler();
    }

    /**
     * Add mock response to response handler
     *
     * @param int $code
     * @param array $headers
     * @param string $body
     * @param string $version
     * @param string|null $reason
     * @return $this
     */
    protected function appendResponse(
        $code = 200,
        array $headers = [],
        $body = '',
        $version = '1.1',
        $reason = null
    ) {
        $this->mockHandler->append(new Response($code, $headers, $body, $version, $reason));
        return $this;
    }

    /**
     * Add mock exception to response handler
     *
     * @param \Exception $exception
     * @return HttpClientMiddlewareTestCase
     */
    protected function appendException(\Exception $exception)
    {
        $this->mockHandler->append($exception);
        return $this;
    }

    /**
     * Get http-client instance
     *
     * @return Client
     */
    public function client()
    {
        $stack = HandlerStack::create($this->mockHandler);
        $middleware = $this->middleware();
        $stack->unshift($middleware->callback(), $middleware->name());

        return new Client(['handler' => $stack]);
    }

    /**
     * Get middleware instance
     *
     * @return MiddlewareInterface
     */
    abstract public function middleware();
}
