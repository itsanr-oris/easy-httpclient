<?php

namespace Foris\Easy\HttpClient\Middleware;

use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware as GuzzleMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class LogMiddleware
 */
class LogMiddleware extends Middleware
{
    /**
     * The middleware executed before current.
     *
     * @var bool
     */
    protected $after = RetryMiddleware::class;

    /**
     * Gets the logger instance
     *
     * @return LoggerInterface
     */
    protected function logger()
    {
        return $this->httpClient()->getLogger();
    }

    /**
     * Gets the log message template.
     *
     * @return mixed|string
     */
    protected function format()
    {
        return $this->getConfig('log_template', MessageFormatter::DEBUG);
    }

    /**
     * Gets the log message record level
     *
     * @return mixed|string
     */
    protected function level()
    {
        return $this->getConfig('log_level', LogLevel::INFO);
    }

    /**
     * Gets the middleware handler.
     *
     * @return callable
     */
    public function callback()
    {
        return GuzzleMiddleware::log($this->logger(), new MessageFormatter($this->format()), $this->level());
    }

    /**
     * Register current middleware.
     */
    public function register()
    {
        if ($this->logger() instanceof LoggerInterface) {
            parent::register();
        }
    }
}
