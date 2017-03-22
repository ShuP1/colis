<?php

namespace Colis;

use Interop\Http\Factory\UriFactoryInterface;

class UriFactory implements UriFactoryInterface
{
    /** Create a new URI */
    public function createUri($uri = '')
    {
        if (empty($uri)) {
            return new Uri();
        }

        if (!is_string($uri) && !method_exists($uri, '__toString')) {
            throw new InvalidArgumentException('Uri must be a string');
        }

        $parts = parse_url($uri);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? $parts['pass'] : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? $parts['port'] : null;
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? $parts['fragment'] : '';

        return new Uri($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    /** Create a new Uri form $_SERVER */
    public function createUriFromServer(array $server)
    {
        if (isset($server['HTTPS'])) {
            if (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off') {
                $scheme = 'https';
            }
        }
        if (!isset($scheme)) {
            $scheme = 'http';
        }

        $username = isset($server['PHP_AUTH_USER']) ? $server['PHP_AUTH_USER'] : '';
        $password = isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : '';

        $host = isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : $server['SERVER_NAME'];

        $port = isset($server['SERVER_PORT']) ? (int)$server['SERVER_PORT'] : 80;
        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];

            if ($matches[2]) {
                $port = (int) substr($matches[2], 1);
            }
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $port = (int) substr($host, $pos + 1);
                $host = strstr($host, ':', true);
            }
        }

        $requestScriptName = parse_url($server['SCRIPT_NAME'], PHP_URL_PATH);
        $requestScriptDir = dirname($requestScriptName);

        $requestUri = parse_url('http://example.com' . $server['REQUEST_URI'], PHP_URL_PATH);

        $basePath = '';
        $virtualPath = $requestUri;
        if (stripos($requestUri, $requestScriptName) === 0) {
            $basePath = $requestScriptName;
        } elseif ($requestScriptDir !== '/' && stripos($requestUri, $requestScriptDir) === 0) {
            $basePath = $requestScriptDir;
        }

        if ($basePath) {
            $virtualPath = ltrim(substr($requestUri, strlen($basePath)), '/');
        }

        $queryString = isset($server['QUERY_STRING']) ? $server['QUERY_STRING'] : '';
        if ($queryString === '') {
            $queryString = parse_url('http://example.com' . $server['REQUEST_URI'], PHP_URL_QUERY);
        }

        $fragment = '';

        $uri = new Uri($scheme, $host, $port, $virtualPath, $queryString, $fragment, $username, $password);
        if ($basePath) {
            $uri = $uri->withBasePath($basePath);
        }

        return $uri;
    }
}
