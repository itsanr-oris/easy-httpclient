<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient\Tests\Middleware;

use Foris\Easy\HttpClient\Middleware\LogMiddleware;
use Foris\Easy\HttpClient\Tests\TestCase;
use GuzzleHttp\MessageFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

/**
 * Class LogMiddlewareTest
 */
class LogMiddlewareTest extends TestCase
{
    /**
     * Gets the logger instance.
     *
     * @return LoggerInterface|TestLogger
     */
    protected function logger()
    {
        return $this->httpClient()->getLogger();
    }

    /**
     * Gets the log message record level.
     *
     * @return array|mixed|null
     */
    protected function level()
    {
        return $this->httpClient()->getConfig('log_level', LogLevel::INFO);
    }

    /**
     * Gets the log message formatter.
     *
     * @return MessageFormatter
     */
    protected function formatter()
    {
        return new MessageFormatter($this->getConfig('log_template', MessageFormatter::DEBUG));
    }

    /**
     * Test whether the component is not loaded.
     */
    public function testWhetherTheLogMiddlewareIsNotLoaded()
    {
        $this->assertFalse(strpos($this->httpClient()->getHandlerStack(), LogMiddleware::name()));
    }

    /**
     * Test whether the component is loaded.
     */
    public function testWhetherTheLogMiddlewareIsLoaded()
    {
        $this->httpClient()->setLogger(new TestLogger());
        $this->assertTrue(strpos($this->httpClient()->getHandlerStack(), LogMiddleware::name()) >= 0);
    }

    /**
     * Test log http message.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testLogHttpRequestMessage()
    {
        $this->httpClient()->setLogger(new TestLogger());

        $this->mockResponse();
        $response = $this->httpClient()->castResponse(false)->get('http://localhost/demo');

        $this->assertCount(1, $this->logger()->records);
        $this->assertSame($this->level(), $this->logger()->records[0]['level']);
        $this->assertSame($this->formatter()->format($this->lastRequest(), $response), $this->logger()->records[0]['message']);
    }
}
