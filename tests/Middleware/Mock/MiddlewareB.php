<?php

namespace Foris\Easy\HttpClient\Tests\Middleware\Mock;

use GuzzleHttp\Middleware as GuzzleMiddleware;
use Foris\Easy\HttpClient\Middleware\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * Class MiddlewareB
 */
class MiddlewareB extends Middleware
{
    /**
     * The middleware executed before current.
     *
     * @var string
     */
    protected $after = MiddlewareA::class;

    /**
     * Gets the middleware handler.
     *
     * @return mixed
     */
    protected function callback()
    {
        return GuzzleMiddleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('T-middleware', $request->getHeaderLine('T-middleware') . 'B');
        });
    }
}
