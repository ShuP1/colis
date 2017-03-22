<?php

namespace Colis;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/** Representation of an outgoing, server-side response */
class Response extends Message implements ResponseInterface
{
    /** Status Code */
    protected $code;

    /** Status Phrase */
    protected $phrase;

    public function __construct($code = 200, StreamInterface $body = null, Headers $headers = null, string $version = null)
    {
        if ($code) {
            $this->code = Validator::checkCode($code);
        }
        parent::__construct($body, $headers, $version);
    }

    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->body = clone $this->body;
    }

    /** Gets the response reason phrase associated with the status code */
    public function getReasonPhrase()
    {
        if ($this->phrase) {
            return $this->phrase;
        }
        return Validator::getCodeMessage($this->code);
    }

    /** Gets the response status code */
    public function getStatusCode()
    {
        return $this->code;
    }

    /** Return an instance with the specified status code and, optionally, reason phrase */
    public function withStatus($code, $reason = '')
    {
        $code = Validator::checkCode($code);

        if (!is_string($reason) && !method_exists($reason, '__toString')) {
            throw new InvalidArgumentException('ReasonPhrase must be a string');
        }

        $clone = clone $this;
        $clone->code = $code;
        if ($reason === '') {
            $reason = Validator::getCodeMessage($code);
        }

        if ($reason === '') {
            throw new InvalidArgumentException('ReasonPhrase must be supplied for this code');
        }

        $clone->phrase = $reason;

        return $clone;
    }
}
