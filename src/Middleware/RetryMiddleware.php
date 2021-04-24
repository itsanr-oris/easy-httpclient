<?php

namespace Foris\Easy\HttpClient\Middleware;

use GuzzleHttp\Middleware as GuzzleMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

/**
 * Class RetryMiddleware
 */
class RetryMiddleware extends Middleware
{
    /**
     * Gets the middleware handler
     *
     * @return callable
     */
    public function callback()
    {
        return GuzzleMiddleware::retry($this->decider(), $this->delay());
    }

    /**
     * Gets the retry times after http request failure.
     *
     * @return int|mixed
     */
    protected function retries()
    {
        return $this->getConfig('max_retries',1);
    }

    /**
     * Gets the retry delay milliseconds after http request failure.
     *
     * @return int|mixed
     */
    protected function delay()
    {
        return function () {
            return $this->getConfig('retry_delay', 500);
        };
    }

    /**
     * Gets the retry decider after http request failure.
     *
     * @return \Closure
     */
    protected function decider()
    {
        return function ($retries, Request $request, Response $response = null, $exception = null) {
            if ($retries >= $this->retries()) {
                return false;
            }

            return $exception instanceof ConnectException
                || $exception instanceof ServerException
                || ($response && $response->getStatusCode() >= 500);
        };
    }
}
