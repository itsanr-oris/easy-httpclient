<?php

namespace Foris\Easy\HttpClient\Http;

use GuzzleHttp\Psr7\Request;

/**
 * Class MultipartParser
 */
class MultipartParser
{
    /**
     * Parse a multi-part request.
     *
     * @param Request $request
     * @return array
     */
    public function parseRequest(Request $request)
    {
        if (!$this->isMultipartRequest($request)) {
            return [];
        }

        return $this->parseBody($request->getBody(), $this->getBoundary($request));
    }

    /**
     * Determine whether a given request is multi-part request.
     *
     * @param Request $request
     * @return bool
     */
    protected function isMultipartRequest(Request $request)
    {
        $header = $request->getHeaderLine('Content-Type');
        return strpos($header, 'multipart/form-data') !== false;
    }

    /**
     * Get multi-part content boundaries.
     *
     * @param Request $request
     * @return mixed
     */
    protected function getBoundary(Request $request)
    {
        $matches = [];
        preg_match('/.*boundary=(.*)$/', $request->getHeaderLine('Content-Type'), $matches);
        return $matches[1];
    }

    /**
     * Parse the content of the multi-part request body.
     *
     * @param $content
     * @param $boundary
     * @return array
     */
    protected function parseBody($content, $boundary)
    {
        $multipart = [];
        $rawParts = preg_split("/[-]+{$boundary}[-]*/", $content);
        array_pop($rawParts);

        foreach ($rawParts as $part) {
            if (empty($part)) {
                continue;
            }

            $multipart[] = $this->parseMultiPart($part);
        }

        return $multipart;
    }

    /**
     * Parse the multi-part content.
     *
     * @param $content
     * @return array
     */
    protected function parseMultiPart($content)
    {
        $rawParts = preg_split('/\\r\\n\\r\\n/', $content, 2);

        $headers = $this->parseHeaders($rawParts[0]);
        list($name, $filename) = $this->parseContentDisposition($this->getHeader($headers, 'Content-Disposition'));
        $contents = mb_substr($rawParts[1], 0, (int) $this->getHeader($headers, 'Content-Length'));

        return ['name' => $name, 'filename' => $filename, 'contents' => $contents, 'headers' => $headers];
    }

    /**
     * Parse the multi-part headers.
     *
     * @param $content
     * @return array
     */
    protected function parseHeaders($content)
    {
        $headers = [];
        $rawHeaders = preg_split('/\\r\\n/', $content);

        foreach ($rawHeaders as $rawHeader) {
            if (strpos($rawHeader, ':') === false) {
                continue;
            }

            list($name, $value) = explode(':', $rawHeader, 2);
            $headers[trim($name)] = trim($value);
        }

        return $headers;
    }

    /**
     * Gets the multi-part header value.
     *
     * @param $headers
     * @param $header
     * @return string
     */
    protected function getHeader($headers, $header)
    {
        return empty($headers[$header]) ? '' : $headers[$header];
    }

    /**
     * Parse the 'Content-Disposition' header and get file information.
     *
     * @param $content
     * @return array
     */
    protected function parseContentDisposition($content)
    {
        if (strpos($content, 'filename') === false) {
            $content .= '; filename=""';
        }

        $matches = [];
        preg_match('/.*name="(.*)".*filename="(.*)".*/', $content, $matches);

        $name = empty($matches[1]) ? '' : $matches[1];
        $filename = empty($matches[2]) ? '' : $matches[2];

        return [$name, $filename];
    }
}
