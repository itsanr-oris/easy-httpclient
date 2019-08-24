<?php

namespace Foris\Easy\HttpClient;

use GuzzleHttp\Psr7\Response;
use Foris\Easy\Support\Collection;

/**
 * Class ResponseCast
 */
class ResponseHandler
{
    const TYPE_ARRAY = 'array';
    const TYPE_COLLECTION = 'collection';
    const TYPE_GUZZLE_RESPONSE = 'guzzle';

    /**
     * Cast response message to specified type
     *
     * @param Response $response
     * @param string   $type
     * @return mixed
     */
    public function castResponse(Response $response, $type = self::TYPE_COLLECTION)
    {
        if ($type == self::TYPE_ARRAY) {
            return $this->castResponseToArray($response);
        }

        if ($type == self::TYPE_COLLECTION) {
            $items = $this->castResponseToArray($response);
            return is_array($items) ? new Collection($items) : $items;
        }

        return $response;
    }

    /**
     * Cast response message to array
     *
     * @param Response $response
     * @return array|mixed
     */
    protected function castResponseToArray(Response $response)
    {
        if (false !== strpos($response->getHeaderLine('Content-Type'), 'application/json')) {
            return json_decode($response->getBody(), true);
        }

        return $response->getBody();
    }
}