<?php

namespace Foris\Easy\HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Foris\Easy\HttpClient\Middleware\MiddlewareInterface;

/**
 * Class HttpClient
 */
class HttpClient
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var HandlerStack
     */
    protected $handlerStack;

    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * @var
     */
    protected $baseUri;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var ResponseHandler
     */
    protected $responseHandler;

    /**
     * @var mixed
     */
    protected $responseType;

    /**
     * @var string
     */
    protected $defaultResponseType = ResponseHandler::TYPE_GUZZLE_RESPONSE;

    /**
     * @var array
     */
    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * HttpClient constructor.
     *
     * @param array           $config
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->setResponseType($config['response_type'] ?? $this->defaultResponseType);
    }

    /**
     * Set http-client config
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config = [])
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Set response handler
     *
     * @param ResponseHandler $handler
     * @return $this
     */
    public function setResponseHandler(ResponseHandler $handler)
    {
        $this->responseHandler = $handler;
        return $this;
    }

    /**
     * Get response handler instance
     *
     * @return ResponseHandler
     */
    public function getResponseHandler()
    {
        if (!$this->responseHandler instanceof ResponseHandler) {
            $this->responseHandler = new ResponseHandler();
        }
        return $this->responseHandler;
    }

    /**
     * Set response message return type
     *
     * @param string $type
     * @return $this
     */
    public function setResponseType($type = ResponseHandler::TYPE_COLLECTION)
    {
        $this->responseType = $type;
        return $this;
    }

    /**
     * Get response message return type
     *
     * @return mixed
     */
    public function getResponseType()
    {
        // reset to default response type
        $type = $this->responseType;
        $this->setResponseType($this->config['response_type'] ?? $this->defaultResponseType);
        return $type;
    }

    /**
     * Push middleware
     *
     * @param MiddlewareInterface $middleware
     * @return $this
     */
    public function pushMiddleware(MiddlewareInterface $middleware)
    {
        $this->middleware[$middleware->name()] = $middleware->callable();
        if ($this->handlerStack instanceof HandlerStack) {
            $this->handlerStack->push($middleware->callable(), $middleware->name());
        }
        return $this;
    }

    /**
     * Remove middleware
     *
     * @param string $name
     * @return $this
     */
    public function removeMiddleware(string $name)
    {
        unset($this->middleware[$name]);
        if ($this->handlerStack instanceof HandlerStack) {
            $this->handlerStack->remove($name);
        }
        return $this;
    }

    /**
     * Set handler-stack instance
     *
     * @param \GuzzleHttp\HandlerStack $handlerStack
     *
     * @return $this
     */
    public function setHandlerStack(HandlerStack $handlerStack)
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    /**
     * Get a handler stack instance
     *
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack(): HandlerStack
    {
        if ($this->handlerStack) {
            return $this->handlerStack;
        }

        $this->handlerStack = HandlerStack::create();

        foreach ($this->middleware as $name => $middleware) {
            $this->handlerStack->push($middleware, $name);
        }

        return $this->handlerStack;
    }

    /**
     * Set guzzle-client instance
     *
     * @param ClientInterface $client
     * @return $this
     */
    public function setGuzzleClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get guzzle-client instance
     *
     * @return Client|ClientInterface
     */
    public function getGuzzleClient()
    {
        if (!($this->client instanceof ClientInterface)) {
            $this->client = new Client($this->config);
        }
        return $this->client;
    }

    /**
     * Get http-client instance
     *
     * @return Client|ClientInterface
     */
    public function client()
    {
        return $this->getGuzzleClient();
    }

    /**
     * Execute get request
     *
     * @param string $url
     * @param array  $query
     * @param array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(
        string $url,
        array $query = [],
        array $options = []
    ) {
        return $this->request($url,'GET', array_merge($options, ['query' => $query]));
    }

    /**
     * Execute post request
     *
     * @param string $url
     * @param array  $data
     * @param array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(
        string $url,
        array $data = [],
        array $options = []
    ) {
        return $this->request($url, 'POST', array_merge($options, ['form_params' => $data]));
    }

    /**
     * Execute post request with json data
     *
     * @param string $url
     * @param array  $data
     * @param array  $query
     * @param array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postJson(
        string $url,
        array $data = [],
        array $query = [],
        array $options = []
    ) {
        return $this->request($url, 'POST', array_merge($options, ['query' => $query, 'json' => $data]));
    }

    /**
     * Execute upload request
     *
     * @param string $url
     * @param array  $files
     * @param array  $form
     * @param array  $query
     * @param array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload(
        string $url,
        array $files = [],
        array $form = [],
        array $query = [],
        array $options = []
    ) {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->request($url, 'POST', array_merge($options, [
            'query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30
        ]));
    }

    /**
     * Execute http request
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $url, $method = 'GET', $options = [])
    {
        $method = strtoupper($method);

        $options = array_merge(self::$defaults, $options, ['handler' => $this->getHandlerStack()]);

        $options = $this->fixJsonIssue($options);

        return $this->getResponseHandler()->castResponse($this->client()->request($method, $url, $options), $this->getResponseType());
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function fixJsonIssue(array $options): array
    {
        if (isset($options['json']) && is_array($options['json'])) {
            $options['headers'] = array_merge($options['headers'] ?? [], ['Content-Type' => 'application/json']);

            if (empty($options['json'])) {
                $options['body'] = \GuzzleHttp\json_encode($options['json'], JSON_FORCE_OBJECT);
            } else {
                $options['body'] = \GuzzleHttp\json_encode($options['json'], JSON_UNESCAPED_UNICODE);
            }

            unset($options['json']);
        }

        return $options;
    }
}