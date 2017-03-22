<?php

namespace Colis;

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/** Represents Uploaded Files */
class UploadedFile implements UploadedFileInterface
{
    /** The client-provided full path to the file */
    public $file;

    /** The client-provided file name */
    protected $name;

    /** The client-provided media type of the file */
    protected $type;

    /** The size of the file in bytes */
    protected $size;

    /** A valid PHP UPLOAD_ERR_xxx code for the file upload */
    protected $error = UPLOAD_ERR_OK;

    /** Indicates if the upload is from a SAPI environment */
    protected $sapi = false;

    /** An optional StreamInterface wrapping the file resource */
    protected $stream;

    /** Indicates if the uploaded file has already been moved */
    protected $moved = false;

    /** Construct a new UploadedFile instance */
    public function __construct($file, $name = null, $type = null, $size = null, $error = UPLOAD_ERR_OK, $sapi = false)
    {
        $this->file = $file;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
        $this->sapi = $sapi;
    }

    /** Retrieve a stream representing the uploaded file */
    public function getStream()
    {
        if ($this->moved) {
            throw new \RuntimeException(sprintf('Uploaded file %1s has already been moved', $this->name));
        }
        if ($this->stream === null) {
            $this->stream = new Stream(fopen($this->file, 'r'));
        }

        return $this->stream;
    }

    /** Move the uploaded file to a new location */
    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new RuntimeException('Uploaded file already moved');
        }

        $targetIsStream = strpos($targetPath, '://') > 0;
        if (!$targetIsStream && !is_writable(dirname($targetPath))) {
            throw new InvalidArgumentException('Upload target path is not writable');
        }

        if ($targetIsStream) {
            if (!copy($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
            if (!unlink($this->file)) {
                throw new RuntimeException(sprintf('Error removing uploaded file %1s', $this->name));
            }
        } elseif ($this->sapi) {
            if (!is_uploaded_file($this->file)) {
                throw new RuntimeException(sprintf('%1s is not a valid uploaded file', $this->file));
            }

            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
        } else {
            if (!rename($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
        }

        $this->moved = true;
    }

    /** Retrieve the error associated with the uploaded file */
    public function getError()
    {
        return $this->error;
    }

    /** Retrieve the filename sent by the client */
    public function getClientFilename()
    {
        return $this->name;
    }

    /** Retrieve the media type sent by the client */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /** Retrieve the file size */
    public function getSize()
    {
        return $this->size;
    }
}
