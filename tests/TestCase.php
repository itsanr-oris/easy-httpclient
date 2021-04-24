<?php

namespace Foris\Easy\HttpClient\Tests;

use Foris\Easy\HttpClient\HttpClient;
use Foris\Easy\HttpClient\Test\HttpTestSuite;

/**
 * Class TestCase
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use HttpTestSuite;

    /**
     * HttpClient实例
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Set up the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->setUpHttpClient();
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->resetHttpTestEnvironment();
    }

    /**
     * Gets the http client instance.
     *
     * @return HttpClient
     */
    protected function httpClient()
    {
        if (!$this->httpClient instanceof HttpClient) {
            $this->httpClient = new HttpClient(require __DIR__ . '/../config.example.php');
        }
        return $this->httpClient;
    }

    /**
     * Gets the http client configurations.
     *
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    protected function getConfig($key = null, $default = null)
    {
        return $this->httpClient()->getConfig($key, $default);
    }
}
