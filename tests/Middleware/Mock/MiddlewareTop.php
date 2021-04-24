<?php

namespace Foris\Easy\HttpClient\Tests\Middleware\Mock;

use GuzzleHttp\Middleware as GuzzleMiddleware;
use Foris\Easy\HttpClient\Middleware\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * Class MiddlewareTop
 */
class MiddlewareTop extends Middleware
{
    /**
     * Determine whether to put the middleware on top of the stack.
     *
     * @var bool
     */
    protected $onTop = true;

    /**
     * Gets the middleware handler.
     *
     * @return callable|mixed
     */
    protected function callback()
    {
        return GuzzleMiddleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('T-middleware', $request->getHeaderLine('T-middleware') . '[');
        });
    }
}
