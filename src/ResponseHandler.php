<?php

namespace Foris\Easy\HttpClient;

use GuzzleHttp\Psr7\Response;

/**
 * Class ResponseCast
 */
class ResponseHandler
{
    /**
     * Cast response message to specified type
     *
     * @param Response $response
     * @param array    $options
     * @return mixed
     */
    public function castResponse(Response $response, $options = [])
    {
        if (!$this->enable($options)) {
            return $response;
        }

        if ($this->isJsonResponse($response)) {
            return $this->castJsonResponse($response);
        }

        if ($this->isXmlResponse($response)) {
            return $this->castXmlResponse($response);
        }

        return $response->getBody();
    }

    /**
     * Determine whether to enable the response conversion logic.
     *
     * @param $options
     * @return bool
     */
    protected function enable($options)
    {
        return isset($options['cast_response']) && $options['cast_response'];
    }

    /**
     * Determine whether current response is a json response.
     *
     * @param Response $response
     * @return bool
     */
    protected function isJsonResponse(Response $response)
    {
        return false !== strpos($response->getHeaderLine('Content-Type'), 'application/json');
    }

    /**
     * Cast json response to array.
     *
     * @param Response $response
     * @return mixed
     */
    protected function castJsonResponse(Response $response)
    {
        return json_decode($response->getBody(), true);
    }

    /**
     * Determine whether current response is a xml response.
     *
     * @param Response $response
     * @return bool
     */
    protected function isXmlResponse(Response $response)
    {
        $line = $response->getHeaderLine('Content-Type');
        return false !== strpos($line, 'text/xml') || false !== strpos($line, 'application/xml');
    }

    /**
     * Cast xml response to array.
     *
     * @param Response $response
     * @return mixed
     */
    protected function castXmlResponse(Response $response)
    {
        $xml = simplexml_load_string($response->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
        return json_decode(json_encode($xml), true);
    }
}
