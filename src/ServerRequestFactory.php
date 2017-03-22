<?php

namespace Colis;

use Interop\Http\Factory\ServerRequestFactoryInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /** Create a new server request */
    public function createServerRequest(array $server, $method = null, $uri = null)
    {
        $method = $method ?: $server['REQUEST_METHOD'];
        if (is_string($uri)) {
            $uri = (new UriFactory())->createUri($uri);
        }

        $uri = $uri ?: (new UriFactory())->createUriFromServer($server);

        $body = (new StreamFactory())->createInputStream();
        $headers = Headers::createFromServer($server);
        $param = $server;
        $cookies = self::parseCookies($headers->get('Cookie', []));
        $version = self::getProtocolVersion($server);

        $request = new ServerRequest($method, $uri, $body, $headers, $param, $cookies, null, $version);

        if ($method === 'POST' &&
            in_array($request->getHeaderObject()->getContentType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            $request = $request->withParsedBody($_POST);
        }

        return $request;
    }

    protected static function getProtocolVersion(array $server)
    {
        if (isset($server['SERVER_PROTOCOL'])) {
            return str_replace('HTTP/', '', $server['SERVER_PROTOCOL']);
        }
        return '1.1';
    }

    protected static function parseCookies(array $header)
    {
        if (is_array($header) === true) {
            $header = isset($header[0]) ? $header[0] : '';
        }

        if (is_string($header) === false) {
            throw new InvalidArgumentException('Cannot parse Cookie data. Header value must be a string.');
        }

        $header = rtrim($header, "\r\n");
        $pieces = preg_split('@[;]\s*@', $header);
        $cookies = [];

        foreach ($pieces as $cookie) {
            $cookie = explode('=', $cookie, 2);

            if (count($cookie) === 2) {
                $key = urldecode($cookie[0]);
                $value = urldecode($cookie[1]);

                if (!isset($cookies[$key])) {
                    $cookies[$key] = $value;
                }
            }
        }

        return $cookies;
    }
}
