<?php

namespace Colis;

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use RuntimeException;

/** Describes a data stream */
class Stream implements StreamInterface
{
    /** Stream */
    protected $stream;

    /** Create a new Stream */
    public function __construct($resource = 'php://temp', $mode = 'w+')
    {
        if (is_string($resource)) {
            $resource = fopen($resource, $mode);
        }
        $this->attach($resource);
    }

    /** Reads all data from the stream into a string, from the beginning to end */
    public function __toString()
    {
        if (!is_resource($this->stream)) {
            return '';
        }

        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /** Attach new resource to this object */
    protected function attach($stream)
    {
        if (is_resource($stream) === false) {
            throw new InvalidArgumentException(__METHOD__ . ' argument must be a valid PHP resource');
        }

        if (is_resource($this->stream) === true) {
            $this->detach();
        }

        $this->stream = $stream;
    }

    /** Closes the stream and any underlying resources */
    public function close()
    {
        if (is_resource($this->stream) === true) {
            fclose($this->stream);
        }

        $this->detach();
    }

    /** Separates any underlying resources from the stream */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;
        return $stream;
    }

    /** Get the size of the stream if known */
    public function getSize()
    {
        if (is_resource($this->stream) === true) {
            $stats = fstat($this->stream);
            if (isset($stats['size'])) {
                return $stats['size'];
            }
        }
        return null;
    }

    /** Returns the current position of the file read/write pointer */
    public function tell()
    {
        if (is_resource($this->stream) !== false) {
            $position = ftell($this->stream);
            if ($position !== false) {
                return $position;
            }
        }
        throw new RuntimeException('Could not get the position of the pointer in stream');
    }

    /** Returns true if the stream is at the end of the stream */
    public function eof()
    {
        return is_resource($this->stream) ? feof($this->stream) : true;
    }

    /** Returns whether or not the stream is seekable */
    public function isSeekable()
    {
        if (is_resource($this->stream) === false) {
            return false;
        }
        return $this->getMetadata()['seekable'];
    }

    /** Seek to a position in the stream */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable() || fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Could not seek in stream');
        }
    }

    /** Seek to the beginning of the stream */
    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->stream) === false) {
            throw new RuntimeException('Could not rewind stream');
        }
    }

    /** Returns whether or not the stream is writable */
    public function isWritable()
    {
        if (is_resource($this->stream) === true) {
            $metadata = $this->getMetadata();
            return Validator::isWritable($metadata);
        }
        return false;
    }

    /** Write data to the stream */
    public function write($string)
    {
        if (!$this->isWritable() || ($written = fwrite($this->stream, $string)) === false) {
            throw new RuntimeException('Could not write to stream');
        }

        return $written;
    }

    /** Returns whether or not the stream is readable */
    public function isReadable()
    {
        if (is_resource($this->stream) === true) {
            $metadata = $this->getMetadata();
            return Validator::isReadable($metadata);
        }
        return false;
    }

    /** Read data from the stream */
    public function read($length)
    {
        if (!$this->isReadable() || ($data = fread($this->stream, $length)) === false) {
            throw new RuntimeException('Could not read from stream');
        }

        return $data;
    }

    /** Returns the remaining contents in a string */
    public function getContents()
    {
        if (!$this->isReadable() || ($contents = stream_get_contents($this->stream)) === false) {
            throw new RuntimeException('Could not get contents of stream');
        }

        return $contents;
    }

    /** Get stream metadata as an associative array or retrieve a specific key */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->stream);
        if (is_null($key) === true) {
            return $meta;
        }

        return isset($meta[$key]) ? $meta[$key] : null;
    }
}
