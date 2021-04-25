<?php /** @noinspection PhpDeprecationInspection */

/** @noinspection PhpInternalEntityUsedInspection */

namespace Foris\Easy\HttpClient\Test;

use Foris\Easy\HttpClient\Http\MultipartParser;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;
use GuzzleHttp\Psr7;

/**
 * Trait HttpTest
 *
 * @mixin \PHPUnit\Framework\TestCase
 * @codeCoverageIgnore
 */
trait HttpTestSuite
{
    /**
     * Guzzle mock handler instance.
     *
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * Determine weather to enable http response mock.
     *
     * @var bool
     */
    protected $httpMock = true;

    /**
     * Http request history.
     *
     * @var array
     */
    protected $historyRequest = [];

    /**
     * Gets the http client instance.
     *
     * @return \Foris\Easy\HttpClient\HttpClient
     */
    abstract function httpClient();

    /**
     * Set up http client instance.
     *
     * @return HttpTestSuite
     */
    protected function setUpHttpClient()
    {
        return $this->setUpMockHandler()->setUpHttpHistory();
    }

    /**
     * Set up http response mock handler.
     *
     * @return $this
     */
    public function setUpMockHandler()
    {
        $handlerStack = $this->httpClient()->getHandlerStack();

        // 模拟http结果响应
        if ($this->httpMock) {
            $handlerStack->setHandler($this->mockHandler(true));
        }

        $this->httpClient()->setHandlerStack($handlerStack);

        return $this;
    }

    /**
     * Set up http request history logger.
     *
     * @return $this
     */
    public function setUpHttpHistory()
    {
        $handlerStack = $this->httpClient()->getHandlerStack();

        // 记录请求历史
        $this->historyRequest = [];
        $handlerStack->push(Middleware::history($this->historyRequest));

        $this->httpClient()->setHandlerStack($handlerStack);

        return $this;
    }

    /**
     * Enable http response mock.
     *
     * @param bool $enable
     * @return $this
     */
    protected function enableHttpMock($enable = true)
    {
        $this->httpMock = $enable;
        return $this;
    }

    /**
     * Disable http response mock.
     *
     * @param bool $disable
     * @return HttpTestSuite
     */
    protected function disableHttpMock($disable = true)
    {
        return $this->enableHttpMock(!$disable);
    }

    /**
     * Gets the mock handler instance.
     *
     * @param bool $newInstance
     * @return MockHandler
     */
    protected function mockHandler($newInstance = false)
    {
        if ($newInstance || empty($this->mockHandler)) {
            $this->mockHandler = new MockHandler();
        }

        return $this->mockHandler;
    }

    /**
     * Mock a http response.
     *
     * @param array $body
     * @param int $code
     * @param array $headers
     * @param string $version
     * @param string|null $reason
     * @return $this
     */
    protected function mockResponse(
        $body = [],
        $code = 200,
        $headers = ['Content-Type' => 'application/json'],
        $version = '1.1',
        $reason = null
    ) {
        $body = is_array($body) ? json_encode($body) : $body;
        $this->mockHandler()->append(new Response($code, $headers, $body, $version, $reason));
        return $this;
    }

    /**
     * Mock a http request exception.
     *
     * @param $exception
     * @return $this
     */
    protected function mockHttpException($exception)
    {
        $this->mockHandler()->append($exception);
        return $this;
    }

    /**
     * Gets the history request.
     *
     * @return array
     */
    protected function historyRequest()
    {
        return $this->historyRequest;
    }

    /**
     * Reset http request history log.
     *
     * @return $this
     */
    protected function resetHttpRequestHistory()
    {
        $this->historyRequest = [];
        return $this;
    }

    /**
     * Reset http response mock queue.
     *
     * @return $this
     */
    protected function resetHttpMockResponse()
    {
        if (method_exists($this->mockHandler(), 'reset')) {
            $this->mockHandler()->reset();
        } else {
            $this->setUpMockHandler();
        }

        return $this;
    }

    /**
     * Reset http test environment.
     *
     * @return $this
     */
    protected function resetHttpTestEnvironment()
    {
        return $this->resetHttpRequestHistory()->resetHttpMockResponse();
    }

    /**
     * Gets the last request.
     *
     * @return \Psr\Http\Message\RequestInterface|Request
     */
    protected function lastRequest()
    {
        return $this->mockHandler()->getLastRequest();
    }

    /**
     * Gets the last request options.
     *
     * @return array
     */
    protected function lastRequestOptions()
    {
        return $this->mockHandler()->getLastOptions();
    }

    /**
     * Assert that no request was sent.
     */
    public function assertNoRequestWasSent()
    {
        $this->assertEmpty($this->historyRequest());
    }

    /**
     * Assert a 'GET' request was sent.
     *
     * @param       $uri
     * @param array $query
     * @param array $options
     */
    public function assertGetRequestWasSent($uri, $query = [], $options = [])
    {
        $this->assertRequestWasSent($uri, 'GET', array_merge($options, ['query' => $query]));
    }

    /**
     * Assert a 'POST' request was sent.
     *
     * @param       $uri
     * @param array $data
     * @param array $options
     */
    public function assertPostRequestWasSent($uri, $data = [], $options = [])
    {
        $this->assertRequestWasSent($uri, 'POST', array_merge($options, ['form_params' => $data]));
    }

    /**
     * Assert a 'POST json' request was sent.
     *
     * @param       $uri
     * @param array $data
     * @param array $options
     */
    public function assertPostJsonRequestWasSent($uri, $data = [], $options = [])
    {
        $this->assertRequestWasSent($uri, 'POST', array_merge($options, ['json' => $data]));
    }

    /**
     * Assert a file upload request was sent.
     *
     * @param       $uri
     * @param array $files
     * @param array $options
     */
    public function assertUploadRequestWasSent($uri, $files = [], $options = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => file_exists($path) ? file_get_contents($path) : $path,
            ];
        }

        $this->assertRequestWasSent($uri, 'POST', array_merge($options, [
            'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30
        ]));
    }

    /**
     * Assert a request was sent.
     *
     * @param       $uri
     * @param       $method
     * @param array $options
     */
    public function assertRequestWasSent($uri, $method = 'GET', $options = [])
    {
        // 转换为完整uri
        $uri = $this->buildUri($uri, $this->httpClient()->getConfig());

        foreach ($this->historyRequest() as $httpRequest) {
            if ($this->isSameRequest($httpRequest['request'], $uri, $method, $options)) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail("Http request not sent!\r\nmethod: {$method}\r\nuri: {$uri}\r\noptions: " . var_export($options, true));
    }

    /**
     * Build a full request uri with the given configurations.
     *
     * @param  string|null $uri
     *
     * @param array        $config
     * @return \Psr\Http\Message\UriInterface
     */
    private function buildUri($uri, array $config)
    {
        // for BC we accept null which would otherwise fail in uri_for
        $uri = Psr7\uri_for($uri === null ? '' : $uri);

        if (isset($config['base_uri'])) {
            $uri = Psr7\UriResolver::resolve(Psr7\uri_for($config['base_uri']), $uri);
        }

        if (isset($config['idn_conversion']) && ($config['idn_conversion'] !== false)) {
            $idnOptions = ($config['idn_conversion'] === true) ? IDNA_DEFAULT : $config['idn_conversion'];
            $uri = Utils::idnUriConvert($uri, $idnOptions);
        }

        return $uri->getScheme() === '' && $uri->getHost() !== '' ? $uri->withScheme('http') : $uri;
    }

    /**
     * Determine whether a request is consistent with the given parameters.
     *
     * @param Request $request
     * @param \Psr\Http\Message\UriInterface  $uri
     * @param string  $method
     * @param array   $options
     * @return bool
     */
    private function isSameRequest(Request $request, $uri, $method, $options)
    {
        return $this->isMethod($request, $method)
            && $this->isUri($request, $uri)
            && $this->withHeaders($request, $options)
            && $this->withQuery($request, $options)
            && $this->withBody($request, $options)
            && $this->withFormParams($request, $options)
            && $this->withJsonParams($request, $options)
            && $this->withMultipart($request, $options);
    }

    /**
     * Determine whether a request is consistent with the given method.
     *
     * @param Request $request
     * @param         $method
     * @return bool
     */
    private function isMethod(Request $request, $method)
    {
        return strtolower($request->getMethod()) == strtolower($method);
    }

    /**
     * Determine whether a request is consistent with the given uri.
     *
     * @param Request $request
     * @param \Psr\Http\Message\UriInterface $uri
     * @return false|int
     */
    private function isUri(Request $request, $uri)
    {
        $requestUri = empty($uri->getQuery()) ? $request->getUri()->withQuery('') : $request->getUri();
        return (string) $requestUri == (string) $uri;
    }

    /**
     * Determine whether the given array is a sub-array of another array
     *
     * @param $needle
     * @param $haystack
     * @return bool
     */
    private function isSubArray($needle, $haystack)
    {
        return array_replace_recursive($haystack, $needle) == $haystack;
    }

    /**
     * Determine whether a request is consistent with the given headers.
     *
     * @param Request $request
     * @param array   $options
     * @return bool
     */
    private function withHeaders(Request $request, $options = [])
    {
        if (!isset($options['headers'])
            || empty($options['headers'])) {
            return true;
        }

        foreach ($options['headers'] as $header => $value) {
            $options['headers'][$header] = is_array($value) ? $this->trimHeaderValues($value) : $this->trimHeaderValues([$value]);
        }

        return $this->isSubArray($options['headers'], $this->getHeaders($request));
    }

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param string[] $values Header values
     *
     * @return string[] Trimmed header values
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function trimHeaderValues(array $values)
    {
        return array_map(function ($value) {
            if (!is_scalar($value) && null !== $value) {
                throw new \InvalidArgumentException(sprintf(
                    'Header value must be scalar or null but %s provided.',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }

            return trim((string) $value, " \t");
        }, array_values($values));
    }

    /**
     * Parse the request and get the headers.
     *
     * @param Request $request
     * @return array
     */
    private function getHeaders(Request $request)
    {
        return $request->getHeaders();
    }

    /**
     * Determine whether a request is consistent with the given query parameters.
     *
     * @param Request $request
     * @param array   $options
     * @return bool
     */
    private function withQuery(Request $request, $options = [])
    {
        if (!isset($options['query'])
            || empty($options['query'])) {
            return true;
        }

        return $this->isSubArray($options['query'], $this->getQuery($request));
    }

    /**
     * Parse the request and get the query parameters.
     *
     * @param Request $request
     * @return array
     */
    private function getQuery(Request $request)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(),$query);
        return $query;
    }

    /**
     * Determine whether a request is consistent with the given body content.
     *
     * @param Request $request
     * @param array   $options
     * @return bool
     */
    private function withBody(Request $request, $options = [])
    {
        if (!isset($options['body'])
            || empty($options['body'])) {
            return true;
        }

        return strpos($request->getBody(), $options['body']) !== false;
    }

    /**
     * Determine whether a request is consistent with the given form parameters.
     *
     * @param Request $request
     * @param array   $options
     * @return bool
     */
    private function withFormParams(Request $request, $options = [])
    {
        if (!isset($options['form_params'])
            || empty($options['form_params'])) {
            return true;
        }

        return $this->isSubArray($options['form_params'], $this->getFormParams($request));
    }

    /**
     * Parse the request and get the form parameters.
     *
     * @param Request $request
     * @return array
     */
    private function getFormParams(Request $request)
    {
        $post = [];

        if (false !== strpos($request->getHeaderLine('Content-Type'), 'application/x-www-form-urlencoded')) {
            parse_str($request->getBody(), $post);
        }

        return $post;
    }

    /**
     * Determine whether a request is consistent with the given json parameters.
     *
     * @param Request $request
     * @param array   $options
     * @return bool
     */
    private function withJsonParams(Request $request, $options = [])
    {
        if (!isset($options['json'])
            || empty($options['json'])) {
            return true;
        }

        return $this->isSubArray($options['json'], $this->getJsonParams($request));
    }

    /**
     * Parse the request and get the json parameters.
     *
     * @param Request $request
     * @return array|mixed
     */
    private function getJsonParams(Request $request)
    {
        if (false !== strpos($request->getHeaderLine('Content-Type'), 'application/json')) {
            return json_decode($request->getBody(), true);
        }

        return [];
    }

    /**
     * Determine whether a request is consistent with the given multi-part parameters.
     *
     * @param Request $request
     * @param array   $options
     * @return bool
     */
    private function withMultipart(Request $request, $options = [])
    {
        if (!isset($options['multipart'])
            || empty($options['multipart'])) {
            return true;
        }

        return $this->isSubArray($options['multipart'], $this->getMultipart($request));
    }

    /**
     * Determine whether the requested multi-part parameter contains the given multi-part content.
     *
     * @param $multipart
     * @param $itemPart
     * @return bool
     */
    protected function hasItemMultiPart($multipart, $itemPart)
    {
        foreach ($multipart as $item) {
            if ($this->isSubArray($itemPart, $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse the request and get the multi-part parameters.
     *
     * @param Request $request
     * @return array
     */
    private function getMultipart(Request $request)
    {
        if (false !== strpos($request->getHeaderLine('Content-Type'), 'multipart/form-data')) {
            return (new MultipartParser)->parseRequest($request);
        }

        return [];
    }
}
