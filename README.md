## Foris/Easy/HttpClient

基于Guzzlehttp组件包封装的Httpclient自用组件包，不建议直接使用

[![Build Status](https://travis-ci.com/itsanr-oris/easy-httpclient.svg?branch=master)](https://travis-ci.com/itsanr-oris/easy-httpclient)
[![codecov](https://codecov.io/gh/itsanr-oris/easy-httpclient/branch/master/graph/badge.svg)](https://codecov.io/gh/itsanr-oris/easy-httpclient)
[![Latest Stable Version](https://poser.pugx.org/f-oris/easy-httpclient/v/stable)](https://packagist.org/packages/f-oris/easy-httpclient)
[![Latest Unstable Version](https://poser.pugx.org/f-oris/easy-httpclient/v/unstable)](https://packagist.org/packages/f-oris/easy-httpclient)
[![Total Downloads](https://poser.pugx.org/f-oris/easy-httpclient/downloads)](https://packagist.org/packages/f-oris/easy-httpclient)
[![License](https://poser.pugx.org/f-oris/easy-httpclient/license)](LICENSE)

## 说明

- [x] 修改部分业务代码逻辑，移除`php-7.0`语法，兼容`php-5.5`语法
- [x] 移除MiddlewareInterface::callable()函数，改为MiddlewareInterface::callback()函数

## 安装使用

```bash
composer require f-oris/easy-httpclient:dev-php-55
```

## License

MIT License

Copyright (c) 2019-present F.oris <us@f-oris.me>
