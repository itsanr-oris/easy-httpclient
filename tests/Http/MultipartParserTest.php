<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\HttpClient\Tests\Http;

use Foris\Easy\HttpClient\Http\MultipartParser;
use Foris\Easy\HttpClient\Tests\TestCase;

/**
 * Class MultipartParserTest
 */
class MultipartParserTest extends TestCase
{
    /**
     * Mock expected multi-part content.
     *
     * @return array
     */
    protected function expectedMultipart()
    {
        return [
            [
                'name' => 'file_1',
                'contents' => file_get_contents(__DIR__ . '/files/file_1.txt'),
            ],
            [
                'name' => 'file_2',
                'contents' => file_get_contents(__DIR__ . '/files/file_2.txt'),
            ],
            [
                'name' => 'file_3',
                'contents' => 'this is a test file content.',
            ]
        ];
    }

    /**
     * Test parse a multi-part request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testParseRequest()
    {
        $files = [
            'file_1' => __DIR__ . '/files/file_1.txt',
            'file_2' => __DIR__ . '/files/file_2.txt',
            'file_3' => 'this is a test file content.',
        ];

        $this->mockResponse();
        $this->httpClient()->upload('http://localhost/demo', $files);

        $parser = new MultipartParser();
        $multipart = $parser->parseRequest($this->lastRequest());

        foreach ($this->expectedMultipart() as $expected) {
            $this->assertTrue($this->hasItemMultiPart($multipart, $expected));
        }
    }

    /**
     * Test parse a non-multi-part request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testParseIllegalMultipartRequest()
    {
        $this->mockResponse();
        $this->httpClient()->get('http://localhost/demo');

        $parser = new MultipartParser();
        $this->assertEmpty($parser->parseRequest($this->lastRequest()));
    }
}
