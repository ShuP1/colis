<?php

namespace Colis;

use Interop\Http\Factory\ResponseFactoryInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    /** Create a new request */
    public function createResponse($code = 200)
    {
        return new Response($code);
    }
}
