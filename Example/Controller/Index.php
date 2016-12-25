<?php

namespace Example\Controller;

use Example\Data\Log;
use Parith\Lib\URI;

class Index extends Basic
{
    /**
     * http://domain/
     */
    public function index()
    {
        $params = $this->routeParams(2);
        $params = http_build_query($params);

        $url = URI::url();

        $str = "<p>Parith Framework is working on page: $url<p>";

        if ($params) {
            $str .= "<p>URL parameters: $params</p>";
        } else {
            $str .= "<p>Try to visit: <a href='$url/Hello/world'>$url/Hello/world<a></p>";
        }

        respOk("<pre>$str</pre>", 'index');
    }

    /**
     * http://domain/Index/databaseLog
     */
    public function databaseLog()
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