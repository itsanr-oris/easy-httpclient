<?php

return [
    /**
     * http request base uri.
     */
    'base_uri' => '',

    /**
     * max retry times
     */
    'max_retries' => 1,

    /**
     * retry delay time, default 500ms
     */
    'retry_delay' => 500,

    /**
     * log level
     */
    'log_level' => \Psr\Log\LogLevel::DEBUG,

    /**
     * request log template
     */
    'log_template' => \GuzzleHttp\MessageFormatter::DEBUG,
];
