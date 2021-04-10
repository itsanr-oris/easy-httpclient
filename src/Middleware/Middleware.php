<?php

namespace Foris\Easy\HttpClient\Middleware;

use Foris\Easy\HttpClient\HttpClient;

/**
 * Class Middleware
 */
abstract class Middleware
{
    /**
     * The name for current middleware.
     *
     * @var string
     */
    protected static $name = '';

    /**
     * The middleware executed after current.
     *
     * @var string
     */
    protected $before = '';

    /**
     * The middleware executed before current.
     *
     * @var string
     */
    protected $after = '';

    /**
     * Determine whether to put the middleware on the top of the stack.
     *
     * @var bool
     */
    protected $onTop = false;

    /**
     * Http client instance.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Middleware constructor.
     *
     * @param HttpClient $httpClient
     */
    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Gets the http-client instance.
     *
     * @return HttpClient
     */
    protected function httpClient()
    {
        return $this->httpClient;
    }

    /**
     * Gets the http-client configuration.
     *
     * @param null $key
     * @param null $default
     * @return array
     */
    protected function getConfig($key = null, $default = null)
    {
        return $this->httpClient()->getConfig($key, $default);
    }

    /**
     * The middleware executed before current.
     *
     * @return string
     */
    protected function before()
    {
        return static::resolveName($this->before);
    }

    /**
     * The middleware executed after current.
     *
     * @return string
     */
    protected function after()
    {
        return static::resolveName($this->after);
    }

    /**
     * Resolve middleware name.
     *
     * @param $name
     * @return mixed
     */
    public static function resolveName($name)
    {
        if (class_exists($name) && is_subclass_of($name, Middleware::class)) {
            return call_user_func_array([$name, 'name'], []);
        }

        return $name;
    }

    /**
     * Determine whether to put the middleware on top of the stack.
     *
     * @return bool
     */
    protected function onTop()
    {
        return $this->onTop;
    }

    /**
     * Determine whether to put the middleware on bottom of the stack.
     *
     * @return bool
     */
    protected function onBottom()
    {
        return !$this->onTop() && empty($this->before()) && empty($this->after());
    }

    /**
     * Gets the middleware name.
     *
     * @return string
     */
    public static function name()
    {
        return empty(static::$name) ? static::class : static::$name;
    }

    /**
     * Gets the middleware handler.
     *
     * @return mixed
     */
    abstract protected function callback();

    /**
     * Register current middleware.
     */
    public function register()
    {
        if ($this->onTop()) {
            $this->httpClient()->middlewareOnTop($this->callback(), $this->name());
            return ;
        }

        if (!empty($this->before())) {
            $this->httpClient()->middlewareBefore($this->before(), $this->callback(), $this->name());
            return ;
        }

        if (!empty($this->after())) {
            $this->httpClient()->middlewareAfter($this->after(), $this->callback(), $this->name());
            return ;
        }

        /**
         * Puts the middleware on the bottom of the stack by default.
         */
        $this->onBottom() && $this->httpClient()->middlewareOnBottom($this->callback(), $this->name());
    }
}
