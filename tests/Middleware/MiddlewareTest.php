<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient\Tests\Middleware;

use Foris\Easy\HttpClient\Tests\Middleware\Mock\MiddlewareA;
use Foris\Easy\HttpClient\Tests\Middleware\Mock\MiddlewareB;
use Foris\Easy\HttpClient\Tests\Middleware\Mock\MiddlewareBottom;
use Foris\Easy\HttpClient\Tests\Middleware\Mock\MiddlewareC;
use Foris\Easy\HttpClient\Tests\Middleware\Mock\MiddlewareD;
use Foris\Easy\HttpClient\Tests\Middleware\Mock\MiddlewareTop;
use Foris\Easy\HttpClient\Tests\TestCase;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * Class MiddlewareTest
 */
class MiddlewareTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->httpClient()->registerMiddleware(MiddlewareA::class);
        $this->httpClient()->registerMiddleware(MiddlewareD::class);
    }

    /**
     * Test register middleware as normal.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testNormalRegisterMiddleware()
    {
        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $request = $this->lastRequest();
        $this->assertEquals('AD', $request->getHeaderLine('T-middleware'));

        return $this->httpClient();
    }

    /**
     * Test register middleware after name given.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRegisterMiddlewareAfterGiven()
    {
        $this->httpClient()->registerMiddleware(MiddlewareB::class);

        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $request = $this->lastRequest();
        $this->assertEquals('ABD', $request->getHeaderLine('T-middleware'));
    }

    /**
     * Test register middleware before name given.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRegisterMiddlewareBeforeGiven()
    {
        $this->httpClient()->registerMiddleware(MiddlewareC::class);

        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $request = $this->lastRequest();
        $this->assertEquals('ACD', $request->getHeaderLine('T-middleware'));
    }

    /**
     * Test register middleware and put it on the top of stack.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRegisterMiddlewareOnTop()
    {
        $this->httpClient()->registerMiddleware(MiddlewareTop::class);

        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $request = $this->lastRequest();
        $this->assertEquals('[AD', $request->getHeaderLine('T-middleware'));
    }

    /**
     * Test register middleware and put it on the bottom of stack.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRegisterMiddlewareOnBottom()
    {
        $this->httpClient()->registerMiddleware(MiddlewareBottom::class);

        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $request = $this->lastRequest();
        $this->assertEquals('AD]', $request->getHeaderLine('T-middleware'));
    }

    /**
     * Test remove a middleware with the given name.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRemoveGivenMiddleware()
    {
        $this->httpClient()->removeMiddleware(MiddlewareA::class);

        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $request = $this->lastRequest();
        $this->assertEquals('D', $request->getHeaderLine('T-middleware'));
    }

    /**
     * Test register a callable type middleware.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRegisterCallableTypeMiddleware()
    {
        $this->httpClient()->registerMiddleware(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('T-callable-middleware', 'message from callable middleware.');
        }));

        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $request = $this->lastRequest();
        $this->assertEquals('message from callable middleware.', $request->getHeaderLine('T-callable-middleware'));
    }
}
