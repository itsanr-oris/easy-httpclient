<?php

namespace Foris\Easy\HttpClient\Middleware;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

/**
 * Class RetryMiddleware
 */
class RetryMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * RetryMiddleware constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get http request failure retry times
     *
     * @return int|mixed
     */
    protected function retries()
    {
        return $this->config['max_retries'] ?? 1;
    }

    /**
     * Get http request failure retry delay
     *
     * @return int|mixed
     */
    protected function delay()
    {
        return function () {
            return $this->config['retry_delay'] ?? 500;
        };
    }

    /**
     * Get http request failure retry decider
     *
     * @return \Closure
     */
    protected function decider()
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            $exception = null
        ) {
            if ($retries >= $this->retries()) {
                return false;
            }

            if ($exception instanceof ConnectException
                || $exception instanceof ServerException) {
                return true;
            }

            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }

            return false;
        };
    }

    /**
     * Get middleware name
     *
     * @return string
     */
    public function name(): string
    {
        return 'retry';
    }

    /**
     * Get middleware closure
     *
     * @return callable
     */
    public function callable(): callable
    {
        return Middleware::retry($this->decider(), $this->delay());
    }
}
