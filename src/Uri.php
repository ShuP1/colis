<?php

namespace Colis;

use \Psr\Http\Message\UriInterface;
use InvalidArgumentException;

/** Value object representing a URI */
class Uri implements UriInterface
{
    /** Uri scheme (without "://" suffix) */
    protected $scheme = '';

    /** Uri user */
    protected $user = '';

    /** Uri password */
    protected $password = '';

    /** Uri host */
    protected $host = '';

    /** Uri port number */
    protected $port;

    /** Uri base path */
    protected $basePath = '';

    /** Uri path */
    protected $path = '';

    /** Uri query string (without "?" prefix) */
    protected $query = '';

    /** Uri fragment string (without "#" prefix) */
    protected $fragment = '';

    /** Create new Uri */
    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    ) {
        $this->scheme = Validator::checkUriScheme($scheme);
        $this->host = $host;
        $this->port = Validator::checkPort($port);
        $this->path = empty($path) ? '/' : Validator::checkPath($path);
        $this->query = Validator::checkQuery($query);
        $this->fragment = Validator::checkQuery($fragment);
        $this->user = $user;
        $this->password = $password;
    }

    /** Retrieve the scheme component of the URI */
    public function getScheme()
    {
        return $this->scheme;
    }

    /** Return an instance with the specified scheme */
    public function withScheme($scheme)
    {
        $scheme = Validator::checkUriScheme($scheme);
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /** Retrieve the authority component of the URI */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        return ($userInfo ? $userInfo . '@' : '') . $host . ($port !== null ? ':' . $port : '');
    }

    /** Retrieve the user information component of the URI */
    public function getUserInfo()
    {
        return $this->user . ($this->password ? ':' . $this->password : '');
    }

    /** Return an instance with the specified user information */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password ? $password : '';

        return $clone;
    }

    /** Retrieve the host component of the URI */
    public function getHost()
    {
        return $this->host;
    }

    /** Return an instance with the specified host */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /** Retrieve the port component of the URI */
    public function getPort()
    {
        return $this->port && !Validator::isStandardPort($this->port) ? $this->port : null;
    }

    /** Return an instance with the specified port */
    public function withPort($port)
    {
        $port = Validator::checkPort($port);
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /** Retrieve the path component of the URI */
    public function getPath()
    {
        return $this->path;
    }

    /** Return an instance with the specified path */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Uri path must be a string');
        }

        $clone = clone $this;
        $clone->path = Validator::checkPath($path);

        // if the path is absolute, then clear basePath
        if (substr($path, 0, 1) == '/') {
            $clone->basePath = '';
        }

        return $clone;
    }

    /** Retrieve the base path segment of the URI */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set base path
     * Note: This method is not part of the PSR-7 standard
     */
    public function withBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new InvalidArgumentException('Uri path must be a string');
        }
        if (!empty($basePath)) {
            $basePath = '/' . trim($basePath, '/');
        }
        $clone = clone $this;

        if ($basePath !== '/') {
            $clone->basePath = Validator::checkPath($basePath);
        }

        return $clone;
    }

    /** Retrieve the query string of the URI */
    public function getQuery()
    {
        return $this->query;
    }

    /** Return an instance with the specified query string */
    public function withQuery($query)
    {
        if (!is_string($query) && !method_exists($query, '__toString')) {
            throw new InvalidArgumentException('Uri query must be a string');
        }
        $query = ltrim((string)$query, '?');
        $clone = clone $this;
        $clone->query = Validator::checkQuery($query);

        return $clone;
    }

    /** Retrieve the fragment component of the URI */
    public function getFragment()
    {
        return $this->fragment;
    }

    /** Return an instance with the specified URI fragment */
    public function withFragment($fragment)
    {
        if (!is_string($fragment) && !method_exists($fragment, '__toString')) {
            throw new InvalidArgumentException('Uri fragment must be a string');
        }
        $fragment = ltrim((string)$fragment, '#');
        $clone = clone $this;
        $clone->fragment = Validator::checkQuery($fragment);

        return $clone;
    }

    /** Return the string representation as a URI reference */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $path = $basePath . '/' . ltrim($path, '/');

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . $path
            . ($query ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');
    }

    /** Return the fully qualified base URL */
    public function getBaseUrl()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();

        if ($authority && substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . rtrim($basePath, '/');
    }
}
