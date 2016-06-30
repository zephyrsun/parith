<?php

namespace Example\Controller;

use Example\Response;

class Index extends Basic
{
    public function index()
    {
        Response::ok('Hello world!', 'index');
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