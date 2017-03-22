<?php

namespace Colis;

use Interop\Http\Factory\RequestFactoryInterface;

class RequestFactory implements RequestFactoryInterface
{
    /** Create a new request */
    public function createRequest($method, $uri)
    {
        if (is_string($uri)) {
            $uri = (new UriFactory())->createUri($uri);
        }
        return new Request($method, $uri);
    }

    /** Non-PSR */
    public function createFullRequest(string $method, UriInterface $uri, StreamInterface $body, Headers $headers, string $version)
    {
        return new Request($method, $uri, $body, $headers, $version);
    }
}
