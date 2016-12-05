<?php

namespace Example\Controller;

use Example\Response;
use Parith\Lib\URI;

class Index extends Basic
{
    public function index()
    {
        Response::ok("<pre>Parith Framework is working on page: " . URI::url() . '</pre>', 'index');
    }

    /**
     * http://example.com/index/simError
     */
    public function simError()
    {
        1 / 0;
    }

    /**
     * http://example.com/index/simException
     */
    public function simException()
    {
        throw  new \Exception('this is Exception');
    }
} 