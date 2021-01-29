<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpParamsInspection */
/** @noinspection PhpMethodParametersCountMismatchInspection */

namespace Foris\Easy\HttpClient\Tests;

use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Foris\Easy\HttpClient\Test\TestCase;
use Foris\Easy\HttpClient\HttpClient;
use Foris\Easy\HttpClient\ResponseHandler;
use Foris\Easy\HttpClient\Middleware\MiddlewareInterface;

/**
 * Class HttpClientTest
 */
class HttpClientTest extends TestCase
{
    /**
     * test get guzzle client
     */
    public function testGetGuzzleClient()
    {
        $this->assertInstanceOf(ClientInterface::class, (new HttpClient())->getGuzzleClient());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testHttpGet()
    {
        $handlerStack = HandlerStack::create();
        $response = new Response();
        $guzzleClient = Mockery::mock(Client::class);

        $options = [
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
            'query' => [
                'key' => 'value'
            ],
            'handler' => $handlerStack,
        ];

        $guzzleClient->shouldReceive('request')->withArgs(['GET', '/', $options])->andReturn($response);

        $httpClient = (new HttpClient())->setGuzzleClient($guzzleClient)->setHandlerStack($handlerStack);

        $this->assertSame($response, $httpClient->get('/', ['key' => 'value']));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testHttpPost()
    {
        $handlerStack = HandlerStack::create();
        $response = new Response();
        $guzzleClient = Mockery::mock(Client::class);

        $options = [
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
            'form_params' => [
                'key' => 'value'
            ],
            'handler' => $handlerStack,
        ];

        $guzzleClient->shouldReceive('request')->withArgs(['POST', '/', $options])->andReturn($response);

        $httpClient = (new HttpClient())->setGuzzleClient($guzzleClient)->setHandlerStack($handlerStack);

        $this->assertSame($response, $httpClient->post('/', ['key' => 'value']));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testHttpPostJson()
    {
        $handlerStack = HandlerStack::create();
        $response = new Response();
        $guzzleClient = Mockery::mock(Client::class);

        $options = [
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
            'query' => [
                'key' => 'value'
            ],
            'handler' => $handlerStack,
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => \GuzzleHttp\json_encode(['data_key' => 'data_value'], JSON_UNESCAPED_UNICODE)
        ];

        $guzzleClient->shouldReceive('request')->withArgs(['POST', '/', $options])->andReturn($response);

        $httpClient = (new HttpClient())->setGuzzleClient($guzzleClient)->setHandlerStack($handlerStack);

        $this->assertSame($response, $httpClient->postJson('/', ['data_key' => 'data_value'], ['key' => 'value']));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testHttpPostEmptyJson()
    {
        $handlerStack = HandlerStack::create();
        $response = new Response();
        $guzzleClient = Mockery::mock(Client::class);

        $options = [
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
            'query' => [
                'key' => 'value'
            ],
            'handler' => $handlerStack,
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => \GuzzleHttp\json_encode([], JSON_FORCE_OBJECT)
        ];

        $guzzleClient->shouldReceive('request')->withArgs(['POST', '/', $options])->andReturn($response);

        $httpClient = (new HttpClient())->setGuzzleClient($guzzleClient)->setHandlerStack($handlerStack);

        $this->assertSame($response, $httpClient->postJson('/', [], ['key' => 'value']));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testHttpUpload()
    {
        $handlerStack = HandlerStack::create();
        $response = new Response();
        $guzzleClient = Mockery::mock(Client::class);

        $guzzleClient->shouldReceive('request')
            ->andReturn($response)
            ->matchArgs([
                'POST',
                '/',
                Mockery::on(function ($options) {
                    $keys = ['curl', 'query', 'multipart', 'connect_timeout', 'timeout', 'read_timeout', 'handler'];
                    if (!empty(array_diff($keys, array_keys($options)))) {
                        return false;
                    }

                    $upload = [];
                    foreach ($options['multipart'] as $multipart) {
                        $upload[] = $multipart['name'];
                    }

                    if (!empty(array_diff(['file', 'form'], $upload))) {
                        return false;
                    }

                    return true;
                })
            ]);

        $httpClient = (new HttpClient())->setGuzzleClient($guzzleClient)->setHandlerStack($handlerStack);

        $uri = '/';
        $files = ['file' => __DIR__ . '/HttpClientTest.php'];
        $form = ['form' => 'form upload data'];
        $query = ['key' => 'value'];

        $this->assertEquals($response, $httpClient->upload($uri, $files, $form, $query));
    }

    /**
     * Push guzzle client middleware before handler stack create
     */
    public function testPushGuzzleClientMiddlewareBeforeHandlerStackCreate()
    {
        $middleware = Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('name')->andReturn('test-middleware');

        $callable = function () {
            return 'This is a test middleware';
        };
        $middleware->shouldReceive('callback')->andReturn($callable);

        $httpClient = new HttpClient();
        $handlerStack = $httpClient->getHandlerStack();
        $expectHandlerStack = HandlerStack::create();
        $expectHandlerStack->push($callable, 'test-middleware');
        $this->assertNotEquals($expectHandlerStack, $handlerStack);

        $httpClient->pushMiddleware($middleware);
        $this->assertEquals($expectHandlerStack, $handlerStack);

        return $httpClient;
    }

    /**
     * Push guzzle client middleware after handler stack create
     */
    public function testPushGuzzleClientMiddlewareAfterHandlerStackCreate()
    {
        $middleware = Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('name')->andReturn('test-middleware');

        $callable = function () {
            return 'This is a test middleware';
        };
        $middleware->shouldReceive('callback')->andReturn($callable);

        $httpClient = new HttpClient();
        $httpClient->pushMiddleware($middleware);
        $handlerStack = $httpClient->getHandlerStack();

        $expectHandlerStack = HandlerStack::create();
        $expectHandlerStack->push($callable, 'test-middleware');

        $this->assertEquals($expectHandlerStack, $handlerStack);

        return $httpClient;
    }

    /**
     * @param HttpClient $httpClient
     * @depends testPushGuzzleClientMiddlewareBeforeHandlerStackCreate
     */
    public function testRemoveGuzzleClientMiddleware(HttpClient $httpClient)
    {
        $httpClient->removeMiddleware('test-middleware');
        $this->assertEquals(HandlerStack::create(), $httpClient->getHandlerStack());
    }

    /**
     * test set response handler
     */
    public function testSetResponseHandler()
    {
        $httpClient = new HttpClient();
        $responseHandler = Mockery::mock(ResponseHandler::class);
        $this->assertSame($responseHandler, $httpClient->setResponseHandler($responseHandler)->getResponseHandler());
    }

    /**
     * test set response type
     */
    public function testSetResponseType()
    {
        $httpClient = new HttpClient();

        $this->assertSame(ResponseHandler::TYPE_GUZZLE_RESPONSE, $httpClient->getResponseType());

        $this->assertSame(
            ResponseHandler::TYPE_COLLECTION,
            $httpClient->setResponseType(ResponseHandler::TYPE_COLLECTION)->getResponseType()
        );
    }
}
