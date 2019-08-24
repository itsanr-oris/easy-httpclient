<?php

return [
    /**
     * request log template
     */
    'log_template' => \GuzzleHttp\MessageFormatter::DEBUG,

    /**
     * log level
     */
    'log_level' => \Psr\Log\LogLevel::DEBUG,

    /**
     * max retry times
     */
    'max_retries' => 1,

    /**
     * retry delay time, default 500ms
     */
    'retry_delay' => 500,

    /**
     * set default response type
     */
    'response_type' => \Foris\Easy\HttpClient\ResponseHandler::TYPE_COLLECTION,
];