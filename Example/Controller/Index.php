<?php

namespace Example\Controller;

use Example\Data\Log;
use Parith\DataSource\PDO;
use Parith\Lib\URI;

class Index extends Basic
{
    /**
     * http://domain/
     */
    public function index()
    {
        respOk("<pre>Parith Framework is working on page: " . URI::url() . '</pre>', 'index');
    }

    /**
     * http://domain/Index/log
     */
    public function log()
    {
        echo '<pre>';

        $log = new Log();
        $result = $log->add('test from Controller');
        echo 'add result:' . $result . PHP_EOL;

        $list = $log->select('id,time')->paginate(30);

        echo 'list:' . PHP_EOL;
        foreach ($list as $row) {
            echo $row['time'] . PHP_EOL;
        }

        echo $list->render() . PHP_EOL;
        echo '</pre>';
    }

    /**
     * http://domain/index/simError
     */
    public function simError()
    {
        1 / 0;
    }

    /**
     * http://domain/index/simException
     */
    public function simException()
    {
        throw  new \Exception('this is Exception');
    }
} 