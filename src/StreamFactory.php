<?php

namespace Colis;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    /** Create a new stream from a string */
    public function createStream($contents = '')
    {
        $stream = new Stream();
        if ($contents) {
            $stream->write($contents);
        }
        return $stream;
    }

    /** Create a stream from an existing file */
    public function createStreamFromFile($file, $mode = 'r')
    {
        return new Stream($file, $mode);
    }

    /** Create a new stream from an existing resource */
    public function createStreamFromResource($resource)
    {
        return new Stream($resource);
    }

    /** Non-Psr */
    public function createInputStream()
    {
        return new Stream('php://input', 'r');
    }

    /** Non-Psr */
    public function createOutputStream()
    {
        return new Stream('php://output', 'w');
    }

    /** Non-Psr */
    public function createMemoryStream()
    {
        return new Stream('php://memory');
    }

    /** Non-Psr */
    public function copyTo(StreamInterface $source, StreamInterface $dest)
    {
        $source->rewind();
        $dest->write($source->getContents());
        return $dest;
    }
}
