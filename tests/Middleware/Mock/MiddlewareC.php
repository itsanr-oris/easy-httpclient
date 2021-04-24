<?php

namespace Foris\Easy\HttpClient\Tests\Middleware\Mock;

use GuzzleHttp\Middleware as GuzzleMiddleware;
use Foris\Easy\HttpClient\Middleware\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * Class MiddlewareC
 */
class MiddlewareC extends Middleware
{
    /**
     * The middleware executed after current.
     *
     * @var string
     */
    protected $before = MiddlewareD::class;

    /**
     * Gets the middleware handler.
     *
     * @return callable|mixed
     */
    protected function callback()
    {
        return GuzzleMiddleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('T-middleware', $request->getHeaderLine('T-middleware') . 'C');
        });
    }
}
