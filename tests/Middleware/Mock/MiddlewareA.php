<?php

namespace Foris\Easy\HttpClient\Tests\Middleware\Mock;

use GuzzleHttp\Middleware as GuzzleMiddleware;
use Foris\Easy\HttpClient\Middleware\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * Class MiddlewareA
 */
class MiddlewareA extends Middleware
{
    /**
     * Gets the middleware handler.
     *
     * @return callable|mixed
     */
    protected function callback()
    {
        return GuzzleMiddleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('T-middleware', $request->getHeaderLine('T-middleware') . 'A');
        });
    }
}
