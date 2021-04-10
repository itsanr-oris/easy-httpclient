<?php

namespace Foris\Easy\HttpClient\Tests;

use GuzzleHttp\Psr7\Response;
use Foris\Easy\HttpClient\ResponseHandler;
use SimpleXMLElement;

/**
 * Class ResponseHandlerTest
 */
class ResponseHandlerTest extends TestCase
{
    /**
     * ResponseHandler instance.
     *
     * @var ResponseHandler
     */
    protected $handler;

    /**
     * Gets the response handler instance.
     *
     * @return ResponseHandler
     */
    protected function handler()
    {
        if (!$this->handler instanceof  ResponseHandler) {
            $this->handler = new ResponseHandler();
        }
        return $this->handler;
    }

    /**
     * Test disable response cast.
     */
    public function testDisableResponseCast()
    {
        $response = new Response();
        $this->assertSame($response, $this->handler()->castResponse($response));
        $this->assertSame($response, $this->handler()->castResponse($response, ['cast_response' => false]));
    }

    /**
     * Test cast json response.
     */
    public function testCastJsonResponse()
    {
        $data = ['key' => 'value'];
        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($data));
        $this->assertEquals($data, $this->handler()->castResponse($response, ['cast_response' => true]));
    }

    /**
     * Convert array to xml.
     *
     * @param $array
     * @param SimpleXMLElement $xml
     */
    protected function arrayToXml($array, &$xml)
    {
        foreach($array as $key => $value) {
            if (!is_array($value)) {
                $xml->addChild($key, $value);
                continue;
            }

            if (is_numeric($key)) {
                $this->arrayToXml($value, $subNode);
                continue;
            }

            $subNode = $xml->addChild($key);
            $this->arrayToXml($value, $subNode);
        }
    }

    /**
     * Test cast xml response.
     */
    public function testCastXmlResponse()
    {
        $data = ['key' => 'value'];
        $xml = new SimpleXMLElement('<root/>');
        $this->arrayToXml($data, $xml);

        $response = new Response(200, ['Content-Type' => 'application/xml'], $xml->asXML());
        $this->assertEquals($data, $this->handler()->castResponse($response, ['cast_response' => true]));

        $response = new Response(200, ['Content-Type' => 'text/xml'], $xml->asXML());
        $this->assertEquals($data, $this->handler()->castResponse($response, ['cast_response' => true]));
    }

    /**
     * Test cast normal response.
     */
    public function testCastNormalResponse()
    {
        $data = ['key' => 'value'];
        $response = new Response(200, [], json_encode($data));
        $this->assertEquals(json_encode($data), $this->handler()->castResponse($response, ['cast_response' => true]));
    }
}
