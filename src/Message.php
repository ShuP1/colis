<?php

namespace Colis;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 */
class Message implements MessageInterface
{
    /** @var string */
    protected $protocol = '1.1';

    /** @var Headers */
    protected $headers;

    /** @var StreamInterface */
    protected $body;

    /** Create new Message */
    public function __construct(string $version = null, StreamInterface $body = null, Headers $headers = null)
    {
        if ($version) {
            $this->protocol = Validator::checkProtocolVersion($version);
        }
        $this->body = $body ?: new Stream();
        $this->headers = $headers ?: new Headers();
    }

    /** Retrieves the HTTP protocol version as a string */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /** Return an instance with the specified HTTP protocol version */
    public function withProtocolVersion($version)
    {
        Validator::checkProtocolVersion($version);
        $instance = clone $this;
        $instance->protocol = $version;

        return $instance;
    }

    /** Retrieves all message header values */
    public function getHeaders()
    {
        return $this->headers->getAllOriginal();
    }

    /** Checks if a header exists by the given case-insensitive name */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /** Retrieves a message header value by the given case-insensitive name */
    public function getHeader($name)
    {
        $this->headers->get($name, []);
    }

    /** Retrieves a comma-separated string of the values for a single header */
    public function getHeaderLine($name)
    {
        return $this->headers->getLine($name, '');
    }

    /** Return an instance with the provided value replacing the specified header */
    public function withHeader($name, $value)
    {
        $instance = clone $this;
        $instance->headers->set($name, $value);
        return $instance;
    }

    /** Return an instance with the specified header appended with the given value */
    public function withAddedHeader($name, $value)
    {
        $instance = clone $this;
        $instance->headers->add($name, $value);
        return $instance;
    }

    /** Return an instance without the specified header */
    public function withoutHeader($name)
    {
        $instance = clone $this;
        $instance->headers->remove($name, $value);
        return $instance;
    }

    /** Gets the body of the message */
    public function getBody()
    {
        return $this->body;
    }

    /** Return an instance with the specified message body */
    public function withBody(StreamInterface $body)
    {
        $instance = clone $this;
        $instance->body = $body;
        return $instance;
    }

    /** Non-PSR */
    public function write($data)
    {
        $this->body->write($data);
    }
}
