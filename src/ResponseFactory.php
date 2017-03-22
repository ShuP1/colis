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
}
