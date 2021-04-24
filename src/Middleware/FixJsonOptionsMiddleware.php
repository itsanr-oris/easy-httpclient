<?php

namespace Foris\Easy\HttpClient\Middleware;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

/**
 * Class FixJsonOptionsMiddleware
 */
class FixJsonOptionsMiddleware extends Middleware
{
    /**
     * Build json body.
     *
     * @param $json
     * @return string
     */
    protected function buildJsonBody($json)
    {
        return empty($json)
            ? \GuzzleHttp\json_encode($json, JSON_FORCE_OBJECT)
            : \GuzzleHttp\json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Modify request.
     *
     * @param RequestInterface $request
     * @param                  $options
     * @return RequestInterface
     */
    protected function modifyRequest(RequestInterface $request, $options)
    {
        if (!isset($options['fix_json']) || !is_array($options['fix_json'])) {
            return $request;
        }

        return $request->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor($this->buildJsonBody($options['fix_json'])));
    }

    /**
     * Gets the middleware handler.
     *
     * @return \Closure
     */
    protected function callback()
    {
        return function ($handler) {
            return function (RequestInterface $request, $options = []) use ($handler) {
                return $handler($this->modifyRequest($request, $options), $options);
            };
        };
    }
}
