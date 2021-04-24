<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpParamsInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient;

use Foris\Easy\HttpClient\Middleware\FixJsonOptionsMiddleware;
use Foris\Easy\HttpClient\Middleware\LogMiddleware;
use Foris\Easy\HttpClient\Middleware\Middleware;
use Foris\Easy\HttpClient\Middleware\RetryMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;

/**
 * Class HttpClient
 */
class HttpClient
{
    /**
     * Guzzle http client instance.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Http-client configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Psr\Logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * Guzzle middleware stack.
     *
     * @var HandlerStack
     */
    protected $handlerStack;

    /**
     * Indicates if the http client has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * ResponseHandler instance.
     *
     * @var ResponseHandler
     */
    protected $responseHandler;

    /**
     * Http client middleware array.
     *
     * @var array
     */
    protected $middleware = [
        RetryMiddleware::class,
        FixJsonOptionsMiddleware::class,
        // other middleware...
    ];

    /**
     * Guzzle http-client request options.
     *
     * @var array
     */
    protected $defaults = [
        'cast_response' => true,
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * Current request options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * HttpClient constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->setConfig($config)->boot();
    }

    /**
     * Boot the http-client components.
     */
    protected function boot()
    {
        $this->bootMiddleware();
        $this->booted = true;
    }

    /**
     * Boot the http-client middleware.
     */
    protected function bootMiddleware()
    {
        foreach ($this->middleware as $class) {
            if (class_exists($class) && is_subclass_of($class, Middleware::class)) {
                $this->registerMiddleware($class);
            }
        }
    }

    /**
     * Sets the http client configuration.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig($config = [])
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Gets the http client configuration.
     *
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function getConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    /**
     * Sets the psr logger instance.
     *
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this->registerLogMiddleware();
    }

    /**
     * Gets the psr logger instance.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Register log middleware.
     *
     * @return $this
     */
    protected function registerLogMiddleware()
    {
        (new LogMiddleware($this))->register();
        return $this;
    }

    /**
     * Sets the guzzle client instance
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
     * Gets the guzzle client instance
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
     * Gets the http client instance
     *
     * @return Client|ClientInterface
     */
    public function client()
    {
        return $this->getGuzzleClient();
    }

    /**
     * Sets the handler stack instance
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
     * Gets the handler stack instance
     *
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack()
    {
        if (!$this->handlerStack instanceof HandlerStack) {
            $this->handlerStack = HandlerStack::create();
        }

        return $this->handlerStack;
    }

    /**
     * Put the middleware on the top of the stack.
     *
     * @param callable $middleware
     * @param string   $name
     * @return $this
     */
    public function middlewareOnTop(callable $middleware, $name = '')
    {
        $this->getHandlerStack()->unshift($middleware, $name);
        return $this;
    }

    /**
     * Put the middleware on the bottom of the stack.
     *
     * @param callable $middleware
     * @param string   $name
     * @return $this
     */
    public function middlewareOnBottom(callable $middleware, $name = '')
    {
        $this->getHandlerStack()->push($middleware, $name);
        return $this;
    }

    /**
     * Put the middleware before the name given.
     *
     * @param          $before
     * @param callable $middleware
     * @param string   $name
     * @return $this
     */
    public function middlewareBefore($before, callable $middleware, $name = '')
    {
        $this->getHandlerStack()->before($before, $middleware, $name);
        return $this;
    }

    /**
     * Put the middleware after the name given.
     *
     * @param          $after
     * @param callable $middleware
     * @param string   $name
     * @return $this
     */
    public function middlewareAfter($after, callable $middleware, $name = '')
    {
        $this->getHandlerStack()->after($after, $middleware, $name);
        return $this;
    }

    /**
     * Register middleware.
     *
     * @param        $middleware
     * @param string $name
     * @return $this|HttpClient
     */
    public function registerMiddleware($middleware, $name = '')
    {
        if (is_string($middleware) && class_exists($middleware) && is_subclass_of($middleware, Middleware::class)) {
            call_user_func_array([new $middleware($this), 'register'], []);
            return $this;
        }

        return $this->middlewareOnBottom($middleware, $name);
    }

    /**
     * Remove middleware.
     *
     * @param $middleware
     * @return $this
     */
    public function removeMiddleware($middleware)
    {
        $middleware = is_string($middleware) ? Middleware::resolveName($middleware) : $middleware;
        $this->getHandlerStack()->remove($middleware);
        return $this;
    }

    /**
     * Gets the response handler instance.
     *
     * @return ResponseHandler
     */
    protected function responseHandler()
    {
        if (!$this->responseHandler instanceof ResponseHandler) {
            $this->responseHandler = new ResponseHandler();
        }
        return $this->responseHandler;
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
    public function get($url, $query = [], $options = [])
    {
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
    public function post($url, $data = [], $options = [])
    {
        return $this->request($url, 'POST', array_merge($options, ['form_params' => $data]));
    }

    /**
     * Execute post request with json data
     *
     * @param string $url
     * @param array  $data
     * @param array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postJson($url, $data = [], $options = [])
    {
        return $this->request($url, 'POST', array_merge($options, ['json' => $data]));
    }

    /**
     * Execute upload request
     *
     * @param string $url
     * @param array  $files
     * @param array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload($url, $files = [], $options = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => file_exists($path) ? fopen($path, 'r') : $path,
            ];
        }

        return $this->request($url, 'POST', array_merge($options, [
            'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30
        ]));
    }

    /**
     * Determine weather to handle http errors.
     *
     * @param bool $flag
     * @return $this
     */
    public function httpErrors($flag = true)
    {
        $this->options['http_errors'] = $flag;
        return $this;
    }

    /**
     * Determine weather to cast http response.
     *
     * @param bool $flag
     * @return $this
     */
    public function castResponse($flag = true)
    {
        $this->options['cast_response'] = $flag;
        return $this;
    }

    /**
     * Reset http request options.
     *
     * @return $this
     */
    protected function resetOptions()
    {
        $this->options = [];
        return $this;
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
    public function request($url, $method = 'GET', $options = [])
    {
        $options = array_merge(
            $this->defaults,
            $this->options,
            $options,
            ['handler' => $this->getHandlerStack()]
        );
        $this->resetOptions();

        if (isset($options['json'])) {
            $options['fix_json'] = $options['json'];
        }

        return $this->responseHandler()->castResponse($this->client()->request(strtoupper($method), $url, $options), $options);
    }
}
