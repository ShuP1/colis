<?php

namespace Colis;

use InvalidArgumentException;

/** Validate different vars */
class Validator
{
    /** List of valid HTTP protocol version */
    public const PROTOCOL_VERSIONS = [
        '1.0' => 1,
        '1.1' => 1,
        '2.0' => 1,
    ];

    /** Resource modes */
    public const MODES = [
        'readable' => ['r', 'r+', 'w+', 'a+', 'x+', 'c+'],
        'writable' => ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'],
    ];

    /** List of valid HTTP Method */
    public const METHODS = [
        'CONNECT' => 1,
        'DELETE' => 1,
        'GET' => 1,
        'HEAD' => 1,
        'OPTIONS' => 1,
        'PATCH' => 1,
        'POST' => 1,
        'PUT' => 1,
        'TRACE' => 1
    ];

    /** Convert code to default message */
    public const CODE_MESSAGE = [
        //Information
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        //Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        210 => 'Content Different',
        226 => 'IM Used',
        //Redirect
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Temporary Redirect',
        310 => 'Too many Redirects',
        //Client getErrorCode
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I’m a teapot',
        421 => 'Bad mapping / Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        //Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient storage',
        508 => 'Loop detected',
        510 => 'Not extended',
        511 => 'Network authentication required'
    ];

    public const SPECIAL_KEYS = [
        'CONTENT_TYPE' => 1,
        'CONTENT_LENGTH' => 1,
        'PHP_AUTH_USER' => 1,
        'PHP_AUTH_PW' => 1,
        'PHP_AUTH_DIGEST' => 1,
        'AUTH_TYPE' => 1
    ];

    public static function isProtocolVersion(string $version)
    {
        return isset(self::PROTOCOL_VERSIONS[$version]);
    }

    public static function checkProtocolVersion(string $version)
    {
        if (!self::isProtocolVersion($version)) {
            throw new InvalidArgumentException(
                "HTTP protocol version. Versions are: "
                . implode(', ', array_keys(self::PROTOCOL_VERSIONS))
                . "."
            );
        }
        return $version;
    }

    public static function isWritable($metadata)
    {
        foreach (self::MODES['writable'] as $mode) {
            if (strpos($metadata['mode'], $mode) === 0) {
                return true;
            }
        }
        return false;
    }

    public static function isReadable($metadata)
    {
        foreach (self::MODES['readable'] as $mode) {
            if (strpos($metadata['mode'], $mode) === 0) {
                return true;
            }
        }
        return false;
    }

    public static function isMethod(string $method)
    {
        return isset(self::METHODS[$method]);
    }

    public static function checkMethod(string $method)
    {
        if ($method === null) {
            return $method;
        }

        if (!is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }

        $method = strtoupper($method);
        if (!self::isMethod($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }

        return $method;
    }

    public static function isRequestTarget(string $request)
    {
        return !preg_match('#\s#', $request);
    }

    public static function checkRequestTarget(string $request)
    {
        if (!self::isRequestTarget($request)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; must be a string and cannot contain whitespace'
            );
        }
        return $request;
    }

    public static function checkUriScheme($scheme)
    {
        static $valid = [
            '' => true,
            'https' => true,
            'http' => true,
        ];

        if (!is_string($scheme) && !method_exists($scheme, '__toString')) {
            throw new InvalidArgumentException('Uri scheme must be a string');
        }

        $scheme = str_replace('://', '', strtolower((string)$scheme));
        if (!isset($valid[$scheme])) {
            throw new InvalidArgumentException('Uri scheme must be one of: "", "https", "http"');
        }

        return $scheme;
    }

    public static function isStandardPort($port, $scheme)
    {
        return ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
    }

    public static function checkPort($port)
    {
        if (is_null($port) || (is_integer($port) && ($port >= 1 && $port <= 65535))) {
            return $port;
        }

        throw new InvalidArgumentException('Uri port must be null or an integer between 1 and 65535 (inclusive)');
    }

    public static function checkPath($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    public static function checkQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    public static function getCodeMessage($code)
    {
        if (isset(self::CODE_MESSAGE[$code])) {
            return self::CODE_MESSAGE[$code];
        }
        return '';
    }

    public static function checkCode($status)
    {
        if (!is_integer($status) || $status<100 || $status>599) {
            throw new InvalidArgumentException('Invalid HTTP status code');
        }

        return $status;
    }

    public static function isSpecialServerKey(string $key)
    {
        return isset(self::SPECIAL_KEYS[$key]);
    }
}
