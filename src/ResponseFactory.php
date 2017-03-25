<?php

namespace Colis;

use Interop\Http\Factory\ResponseFactoryInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    /** Create a new request */
    public function createResponse($code = 200)
    {
        return new Response($code);
    }

    /** Non-PSR */
    public function createFullResponse($code, StreamInterface $body, Headers $headers, string $version)
    {
        return new Response($code, $body, $headers, $version);
    }

    /** Non-PSR */
    public function sendResponse(ResponseInterface $response, StreamFactoryInterface $streams)
    {
        header("HTTP/" . ($response->getProtocolVersion() ?: 1.1) . " " . ($response->getStatusCode() ?: 200) . " " . ($response->getReasonPhrase() ?: 'OK'));
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        $body = $response->getBody();
        if (isset($body)) {
            $body->rewind();
            $streams->createStreamFromFile('php://output', 'w')
            ->write($body->getContents());
        }
    }
}
