<?php
/**
 * Created by PhpStorm.
 * User: f-oris
 * Date: 2019/8/21
 * Time: 6:12 PM
 */

namespace Foris\Easy\HttpClient\Tests\Middleware;

use GuzzleHttp\MessageFormatter;
use function GuzzleHttp\Psr7\str;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use Foris\Easy\HttpClient\Middleware\LogMiddleware;
use Foris\Easy\HttpClient\Middleware\MiddlewareInterface;
use Foris\Easy\HttpClient\Test\HttpClientMiddlewareTestCase;

class LogMiddlewareTest extends HttpClientMiddlewareTestCase
{
    /**
     * @var TestLogger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $level;

    /**
     * @return MiddlewareInterface
     */
    public function middleware(): MiddlewareInterface
    {
        $this->logger = new TestLogger();
        $this->formatter = MessageFormatter::DEBUG;
        $this->level = LogLevel::DEBUG;

        return new LogMiddleware($this->logger, ['log_template' => '{response}', 'log_level' => $this->level]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testLogMiddleware()
    {
        $response = $this->appendResponse()->client()->request('GET', '/');

        $this->assertCount(1, $this->logger->records);
        $this->assertSame($this->level, $this->logger->records[0]['level']);
        $this->assertSame(str($response), $this->logger->records[0]['message']);
    }
}