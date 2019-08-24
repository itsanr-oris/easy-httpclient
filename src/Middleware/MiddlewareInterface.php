<?php

namespace Foris\Easy\HttpClient\Middleware;

/**
 * Interface MiddlewareInterface
 */
interface MiddlewareInterface
{
    /**
     * Get middleware name
     *
     * @return string
     */
    public function name() : string;

    /**
     * Get middleware closure
     *
     * @return callable
     */
    public function callable() : callable;
}