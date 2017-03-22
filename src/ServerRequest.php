<?php

namespace Colis;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Inutils\Storage\Collection;
use InvalidArgumentException;

/** Representation of an incoming, server-side HTTP request */
class ServerRequest extends Request implements ServerRequestInterface
{
    protected $params;
    protected $cookies;
    protected $query;
    protected $upload = [];
    protected $parsedBody = false;
    protected $attributes;

    public function __construct(string $method, UriInterface $uri, StreamInterface $body = null, Headers $header = null, array $params = [], array $cookies = [], Collection $attributes = null, string $version = null)
    {
        $attributes = $attributes ?: new Collection();
        parent::__construct($method, $uri, $body, $header, $version);
        $this->params = $params;
        $this->cookies = $cookies;
        $this->attributes = $attributes;
    }

    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->attributes = clone $this->attributes;
        $this->body = clone $this->body;
    }

    /** Retrieve server parameters */
    public function getServerParams()
    {
        return $this->params;
    }

    /** Retrieve cookies */
    public function getCookieParams()
    {
        return $this->cookies;
    }

    /** Return an instance with the specified cookies */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /** Retrieve query string arguments */
    public function getQueryParams()
    {
        if (is_array($this->query)) {
            return $this->query;
        }

        if ($this->uri === null) {
            return [];
        }

        parse_str($this->uri->getQuery(), $this->query);

        return $this->query;
    }

    /** Return an instance with the specified query string arguments */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /** Retrieve normalized file upload data */
    public function getUploadedFiles()
    {
        $this->upload;
    }

    /** Create a new instance with the specified uploaded files */
    public function withUploadedFiles(array $upload)
    {
        $clone = clone $this;
        $clone->upload = $upload;

        return $clone;
    }

    /** Retrieve any parameters provided in the request body */
    public function getParsedBody()
    {
        if ($this->parsedBody || !$this->body) {
            return $this->parsedBody;
        }
        $type = $this->headers->getContentType();
        $body = (string)$this->getBody();
        switch ($type) {
            case 'application/json':
                $this->parsedBody = json_decode($body, true);
                break;
            case 'application/x-www-form-urlencoded':
                parse_str($body, $data);
                $this->parsedBody = $data;
                break;
            case 'text/xml':
                $disabled = libxml_disable_entity_loader(true);
                $xml = simplexml_load_string($body);
                libxml_disable_entity_loader($disabled);
                $this->parsedBody = $xml;
                break;
            default:
                break;
        }
        return $this->parsedBody;
    }

    /** Return an instance with the specified body parameters */
    public function withParsedBody($data)
    {
        if (!is_null($data) && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException('Parsed body value must be an array, an object, or null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /** Retrieve attributes derived from the request */
    public function getAttributes()
    {
        return $this->attributes->getAll();
    }

    /** Retrieve a single derived request attribute */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /** Return an instance with the specified derived request attribute */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes->set($name, $value);

        return $clone;
    }

    /** Return an instance that removes the specified derived request attribute */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        $clone->attributes->remove($name);

        return $clone;
    }
}
