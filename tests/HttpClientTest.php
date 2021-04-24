<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient\Tests;

use GuzzleHttp\Client;

/**
 * Class HttpClientTest
 */
class HttpClientTest extends TestCase
{
    /**
     * Test gets the http client configurations.
     */
    public function testGetConfig()
    {
        $config = [
            'key_1' => 'value_1',
            'key_2' => 'value_2',
        ];
        $this->httpClient()->setConfig($config);

        $this->assertEquals($config, $this->httpClient()->getConfig());
        $this->assertEquals('value_1', $this->httpClient()->getConfig('key_1'));
        $this->assertNull($this->httpClient()->getConfig('key_3'));
        $this->assertEquals('default_value', $this->httpClient()->getConfig('key_3', 'default_value'));
    }

    /**
     * Test sets the real http client.
     */
    public function testSetGuzzleClient()
    {
        $client = new Client();
        $this->assertSame($client, $this->httpClient()->setGuzzleClient($client)->getGuzzleClient());
    }

    /**
     * Test sending request with curl options.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAssertRequestWithCurlOptions()
    {
        $this->mockResponse();
        $this->httpClient()->request('http://localhost/demo');

        $lastOptions = $this->lastRequestOptions();
        $this->assertEquals([CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4], $lastOptions['curl']);
    }

    /**
     * Test sending a 'GET' request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAssertGetRequestWasSent()
    {
        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo', ['key_1' => 'value_1']);
        $this->assertGetRequestWasSent('http://localhost/demo', ['key_1' => 'value_1']);
    }

    /**
     * Test sending a 'POST' request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAssertPostRequestWasSent()
    {
        $this->mockResponse();
        $this->httpClient()->post('http://localhost/demo', ['key' => 'value']);
        $this->assertPostRequestWasSent('http://localhost/demo', ['key' => 'value']);
    }

    /**
     * Test sending a 'POST json' request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAssertPostJsonRequestWasSent()
    {
        $this->mockResponse();
        $this->httpClient()->postJson('http://localhost/demo', ['key' => 'value']);
        $this->assertPostJsonRequestWasSent('http://localhost/demo', ['key' => 'value']);
    }

    /**
     * Test sending a file upload request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAssertUploadRequestWasSent()
    {
        $this->mockResponse();

        $files = [
            'file_1' => __DIR__ . '/HttpClientTest.php',
            'file_2' => 'file_2 content.'
        ];
        $this->httpClient()->upload('http://localhost/demo', $files);
        $this->assertUploadRequestWasSent('http://localhost/demo', $files);
    }

    /**
     * Test sending request with header params.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAssertRequestWithHeaderParams()
    {
        $this->mockResponse();
        $this->httpClient()->request('http://localhost/demo', 'PUT', ['headers' => ['X-Token' => 'token']]);
        $this->assertRequestWasSent('http://localhost/demo', 'PUT', ['headers' => ['X-Token' => 'token']]);
    }

    /**
     * Test sending request with body params.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAssertRequestWithBodyParams()
    {
        $this->mockResponse();
        $this->httpClient()->request('http://localhost/demo', 'PUT', ['body' => 'test body content']);
        $this->assertRequestWasSent('http://localhost/demo', 'PUT', ['body' => 'test body content']);
    }
}
