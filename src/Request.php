<?php

namespace Colis;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/** Representation of an outgoing, client-side request */
class Request extends Message implements RequestInterface
{

    /** The request URI object */
    protected $uri;

    /** The request URI target (path + query string) */
    protected $requestTarget;

    /** The request method */
    protected $method;

    /** Create a new Request */
    public function __construct(string $method, UriInterface $uri, StreamInterface $body = null, Headers $headers = null, string $version = null)
    {
        $this->method = Validator::checkMethod($method);
        $this->uri = $uri;
        parent::__construct($version, $body, $headers);
    }

    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->body = clone $this->body;
    }

    /** Retrieves the message's request target */
    public function getRequestTarget()
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        if ($this->uri === null) {
            return '/';
        }

        $path = $this->uri->getPath();

        $query = $this->uri->getQuery();
        if ($query) {
            $path .= '?' . $query;
        }

        $this->requestTarget = $path;

        return $this->requestTarget;
    }

    /** Return an instance with the specific request-target */
    public function withRequestTarget($requestTarget)
    {
        $requestTarget = Validator::checkRequestTarget($requestTarget);
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /** Retrieves the HTTP method of the request */
    public function getMethod()
    {
        return $this->method;
    }

    /** Return an instance with the provided HTTP method */
    public function withMethod($method)
    {
        $method = Validator::checkMethod($method);
        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

     /** Retrieves the URI instance */
    public function getUri()
    {
        return $this->uri;
    }

    /** Returns an instance with the provided URI */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $clone->headers->set('Host', $uri->getHost());
            }
        } else {
            if ($this->uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeader('Host') === null)) {
                $clone->headers->set('Host', $uri->getHost());
            }
        }

        return $clone;
    }
}
