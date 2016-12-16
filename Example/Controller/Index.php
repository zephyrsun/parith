<?php

namespace Example\Controller;

use Example\Data\Log;
use Parith\Lib\URI;

class Index extends Basic
{
    public function index()
    {
        respOk("<pre>Parith Framework is working on page: " . URI::url() . '</pre>', 'index');
    }

    public function log()
    {
        $result = (new Log())->add('test from Controller');

        respOk($result);
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