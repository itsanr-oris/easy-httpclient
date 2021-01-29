<?php

namespace Foris\Easy\HttpClient\Middleware;

use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class LogMiddleware
 */
class LogMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * LogMiddleware constructor.
     *
     * @param LoggerInterface|null $logger
     * @param array                $config
     */
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Get logger instance
     *
     * @return LoggerInterface
     */
    protected function logger()
    {
        return $this->logger;
    }

    /**
     * Get log message format template
     *
     * @return mixed|string
     */
    protected function format()
    {
        return isset($this->config['log_template']) ? $this->config['log_template'] : MessageFormatter::DEBUG;
    }

    /**
     * Get logger message record level
     *
     * @return mixed|string
     */
    protected function level()
    {
        return isset($this->config['log_level']) ? $this->config['log_level'] : LogLevel::INFO;
    }

    /**
     * Get middleware name
     *
     * @return string
     */
    public function name()
    {
        return 'log';
    }

    /**
     * Get middleware closure
     *
     * @return callable
     */
    public function callback()
    {
        return Middleware::log($this->logger(), new MessageFormatter($this->format()), $this->level());
    }
}
