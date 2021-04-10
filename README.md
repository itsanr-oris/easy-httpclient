## Foris/Easy/HttpClient

基于Guzzlehttp组件包简单封装的Httpclient组件包

[![Build Status](https://travis-ci.com/itsanr-oris/easy-httpclient.svg?branch=master)](https://travis-ci.com/itsanr-oris/easy-httpclient)
[![codecov](https://codecov.io/gh/itsanr-oris/easy-httpclient/branch/master/graph/badge.svg)](https://codecov.io/gh/itsanr-oris/easy-httpclient)
[![Latest Stable Version](https://poser.pugx.org/f-oris/easy-httpclient/v/stable)](https://packagist.org/packages/f-oris/easy-httpclient)
[![Latest Unstable Version](https://poser.pugx.org/f-oris/easy-httpclient/v/unstable)](https://packagist.org/packages/f-oris/easy-httpclient)
[![Total Downloads](https://poser.pugx.org/f-oris/easy-httpclient/downloads)](https://packagist.org/packages/f-oris/easy-httpclient)
[![License](https://poser.pugx.org/f-oris/easy-httpclient/license)](LICENSE)

## 版本说明

| 版本 | php | 说明 |
| ---- | ---- | ---- |
| <1.0 | >=5.5 | 兼容php5.5临时测试版本，非正式版本，后续移除 |
| 1.x | >=7.1 | 正式版本 |
| 2.x | >=5.5 | 正式版本，兼容php5.5 | 

## 1.0 -> 2.0重要更新说明

- [x] 修改部分业务代码逻辑，移除`php-7.0`语法，兼容`php-5.5`语法
- [x] 优化`postJson`、`upload`方法逻辑，移除部分冗余参数
- [x] 新增`HttpTestSuite`测试辅助组件
- [x] 移除`MiddlewareInterface`，改为抽象类`Middleware`
- [x] 优化`Middleware`注册逻辑，增加`middlewareOnTop`, `middlewareOnBottom`, `middlewareAfter`, `middlewareBefore`方法，开发人员可以根据需要注册并调整中间件位置
- [x] 优化`LogMiddleware`、`RetryMiddleware`使用方式，改为默认启用，其中`LogMiddleware`通过`setLogger`方法设置`Psr/LoggerInterface`实例启用
- [x] 移除原`HttpClient`中的`fixJsonIssue`函数，更改为`FixJsonOptionsMiddleware`实现，开发人员可以自主选择是否启用该中间件
- [x] 移除`f-oris/easy-support`扩展包，如有需要，开发人员可以自行引入该组件包进行使用
- [x] 移除`ResponseHandler`中的`RESPONSE_TYPE_`常量定义，不再通过该常量定义对响应结果进行转换
- [x] 增加请求参数`cast_response`，开发人员可以通过传入该请求参数，将`Response`中的`body`转换为`array`或`string`，如不传入，则返回原生`Response`对象

## 安装

```bash
composer require f-oris/easy-httpclient:^2.0
```

## 配置说明

参考`config.example.php`文件说明

## 基本用法

#### 1. 发起GET请求

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();

/**
 * 发起Get请求，下面两种写法等价
 * 
 * 请求链接：http://localhost/test?key=value
 */
$httpClient->get('http://localhost/test', ['key' => 'value']);
$httpClient->request('http://localhost/test', 'GET', ['query' => ['key' => 'value']]);
```

#### 2. POST表单数据

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();

/**
 * 发起Post请求，下面两种写法等价
 * 
 * 请求链接：http://localhost/test
 * 
 * 表单数据：key=value
 */
$httpClient->post('http://localhost/test', ['key' => 'value']);
$httpClient->request('http://localhost/test', 'POST', ['form_params' => ['key' => 'value']]);
```

#### 3. POST json数据

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();

/**
 * 发起Post请求，下面两种写法等价
 * 
 * 请求链接：http://localhost/test
 * 
 * JSON数据：{"key":value"}
 * 
 */
$httpClient->postJson('http://localhost/test', ['key' => 'value']);
$httpClient->request('http://localhost/test', 'POST', ['json' => ['key' => 'value']]);
```

#### 4. 上传文件

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();

/**
 * 发起文件上传请求
 * 
 * 请求地址：http://localhost/upload
 * 
 * 上传文件：__DIR__ . 'config.example.php'
 */
$httpClient->upload('http://localhost/upload', ['file_name' => __DIR__ . '/config.example.php']);
```

#### 5. 发起其他类型请求

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();

/**
 * 发起PUT请求
 * 
 * 请求地址：http://localhost/put
 * 
 * 携带Json数据：{"key" : "value"}
 * 
 * 携带header数据：X-token : mock_token
 */
$httpClient->request('http://localhost/put', 'PUT', ['json' => ['key' => 'value'], 'headers' => ['X-token' => 'mock_token']]);
```

## 请求选项参数说明

#### 1. 默认curl选项参数



#### 2. 自定义选项参数`cast_response`

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();

/**
 * 不传入cast_response参数情况下
 * 
 * 如果接口返回的响应结果为json或xml字符串时，$response为转换后的数组信息，否则$response为响应结果中的请求体字符串
 */
$response = $httpClient->get('http://localhost/demo');

/**
 * 传入cast_response参数情况下
 * 
 * $response为\GuzzleHttp\Psr7\Response对象实例
 */
$response = $httpClient->get('http://localhost/demo-2', [], ['cast_response' => false]);
```

#### 3. 快捷使用函数操作部分请求选项参数

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();

// 关闭http_error
$httpClient->httpErrors(false);

// 关闭请求结果转换
$httpClient->castResponse(false);
```

## 使用中间件

#### 1. 使用日志中间件

```php
<?php

use Psr\Log\Test\TestLogger;
use Foris\Easy\HttpClient\HttpClient;

$httpClient = new HttpClient();
$httpClient->setLogger(new TestLogger());

// 执行请求逻辑
```

> 添加了日志中间件后，再通过httpClient发起网络请求，可以在相关日志内容中找到对应的请求日志

#### 2. 使用重试中间件

```php
<?php

use Foris\Easy\HttpClient\HttpClient;

$config = [
    // ...
    'max_retries' => 1,
    'retry_delay' => 500,
];
$httpClient = new HttpClient($config);

// 执行请求逻辑
```

> 添加重试中间件后，如果服务器响应状态码为'50x'或服务器链接失败时，httpClient会进行重试，直至达到配置中max_retries设定的最大重试次数，max_retries设置为0则不进行重试

#### 3. 使用自定义中间件

自定义中间件的方式有两种，一种是继承`\Foris\Easy\HttpClient\Middleware\Middleware`，并实现`callback`方法，然后通过`HttpClient`的`registerMiddleware`注册中间件；另一种则是使用`middlewareXxx`相关方法进行闭包函数中间件注册。不管是哪种方式，`callback`函数与闭包函数均需遵循`GuzzleHttp`的中间件函数变现规范。

```php
<?php

use GuzzleHttp\Middleware as GuzzleMiddleware;
use Psr\Http\Message\RequestInterface;
use Foris\Easy\HttpClient\HttpClient;
use Foris\Easy\HttpClient\Middleware\Middleware;

/**
* Class CustomMiddleware
 */
class MyMiddleware extends Middleware
{
    /**
     * 中间件回调执行函数，具体实现参考GuzzleHttp中间件相关文档说明
     * 
     * @return callable|Closure
     */
    public function callback()
    {
        return GuzzleMiddleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('X-Foo', 'bar');
        });
    }
}

$httpClient = new HttpClient([]);
$httpClient->registerMiddleware(new MyMiddleware($httpClient));

// 另一种实现方式
// $httpClient->registerMiddleware(GuzzleMiddleware::mapRequest(function (RequestInterface $request) {
//     return $request->withHeader('X-Foo', 'bar');
// }));

// 发起网络请求，如果有启用日志组件的话，可以观察到请求头中会多一个'X-Foo'的请求头，值为'bar'
```

## 测试辅助组件使用

#### 1. 断言请求及参数

```php
<?php

use PHPUnit\Framework\TestCase;
use Foris\Easy\HttpClient\Test\HttpTestSuite;
use Foris\Easy\HttpClient\HttpClient;

class DemoTest extends TestCase
{
    use HttpTestSuite;
    
    /**
     * http-client实例
     * 
     * @var HttpClient
     */
    protected $httpClient;
    
    /**
     * 获取http-client实例
     * 
     * @return HttpClient
     */
    protected function httpClient()
    {
        if ($this->httpClient instanceof HttpClient) {
            $this->httpClient = new HttpClient();
        }
        
        return $this->httpClient;
    }
    
    /**
     * 测试样例
     * 
     * 每个测试开始之前，如果不是真实发起调用，注意要mock一下对应的调用结果，避免Guzzle client调用出错
     */
    public function testDemo()
    {
        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo', ['key' => 'value']);
        
        /**
         * 断言发起Get请求，且携带query string参数`key=value`，同时携带header参数`token=mock_token`
         */
        $this->assertGetRequestWasSent('http://localhost/demo', ['key' => 'value']);
        
        // 其他断言方法
        
        /**
         * 断言发起Post请求，且携带表单参数`key=value`
         */
        $this->assertPostRequestWasSent('http://localhost/demo', ['key' => 'value']);
        
        /**
         * 断言发起Post请求，且携带json参数:{'key' : 'value'}
         */
        $this->assertPostJsonRequestWasSent('http://localhost/demo', ['key' => 'value']);
        
        /**
         * 断言发起文件上传请求，且携带文件参数`file`，文件路径为`/path/file.csv`
         */
        $this->assertUploadRequestWasSent('http://localhost/demo', ['file' => '/path/file.csv']);
        
        /**
         * 断言其他类型请求及参数
         * 
         * 断言发起PUT请求，且携带header参数`X-token=mock_token`
         * 
         * 所有断言方法均可携带$options参数，支持断言$options内的`query`,`body`,`form_params`,`json`,`headers`,`multipart`参数
         */
        $this->assertRequestWasSent('http://localhost/demo', 'PUT', ['headers' => ['X-token' => 'mock_token']]);
    }
}
```

#### 2. 模拟请求结果

```php
<?php

use PHPUnit\Framework\TestCase;
use Foris\Easy\HttpClient\Test\HttpTestSuite;
use Foris\Easy\HttpClient\HttpClient;

class DemoTest extends TestCase
{
    use HttpTestSuite;

    /**
     * http-client实例
     * 
     * @var HttpClient
     */
    protected $httpClient;
    
    /**
     * 获取http-client实例
     * 
     * @return HttpClient
     */
    protected function httpClient()
    {
        if ($this->httpClient instanceof HttpClient) {
            $this->httpClient = new HttpClient();
        }
        
        return $this->httpClient;
    }
    
    /**
     * 测试样例
     */
    public function testDemo()
    {
        /**
         * 模拟一个json响应结果，内容为：{"key":"value"}
         */
        $this->mockResponse(['key' => 'value']);
        
        /**
         * 模拟一个文本内容响应结果，内容为：text
         */
        $this->mockResponse('text', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    
        /**
         * 模拟一个状态码为403的响应结果
         */
        $this->mockResponse('forbidden', 403);
        
        /**
         * 模拟一个携带Header信息，内容为：X-Server: test_server
         */
        $this->mockResponse([], 200, ['X-Server' => 'test_server']);
        
        /**
         * 使用Guzzle Response作为响应结果
         */
        $this->mockHandler()->append(new \GuzzleHttp\Psr7\Response());
    }
}

```

#### 3. 模拟发起请求过程中出现异常

```php
<?php

use PHPUnit\Framework\TestCase;
use Foris\Easy\HttpClient\Test\HttpTestSuite;
use Foris\Easy\HttpClient\HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;

class DemoTest extends TestCase
{
    use HttpTestSuite;

    /**
     * http-client实例
     * 
     * @var HttpClient
     */
    protected $httpClient;
    
    /**
     * 获取http-client实例
     * 
     * @return HttpClient
     */
    protected function httpClient()
    {
        if ($this->httpClient instanceof HttpClient) {
            $this->httpClient = new HttpClient();
        }
        
        return $this->httpClient;
    }
    
    /**
     * 测试样例
     */
    public function testDemo()
    {
        $this->mockHttpException(new ConnectException('connect time out', new Request('GET', 'http://localhost/demo')));
    }
}
```

## License

MIT License

Copyright (c) 2019-present F.oris <us@f-oris.me>
