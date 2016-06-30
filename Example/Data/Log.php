<?php

namespace Example\Data;

/**
 * see doc/example.sql
 */
class Log extends Database
{
    public $cfg_key = 'database_1';
    public $primary = 'id';

    public $table_name = 'logs';

    public function add($data, $code = 0)
    {
        if (is_array($data))
            $data = json_encode($data, \JSON_UNESCAPED_UNICODE);

        $get = &$_SERVER['QUERY_STRING'] or $get = implode('/', \Parith\App::$query);

        $post = $_POST ? http_build_query($_POST) : file_get_contents('php://input');

        return parent::insert(array(
            'get' => $get, //get
            'post' => $post, //post
            'data' => $data,
            'code' => $code,
            'srv_ip' => &$_SERVER['SERVER_ADDR'],
            'client_ip' => \Parith\Lib\Request::getClientIp(),
            'time' => \date("Y-m-d H:i:s", \APP_TS), //time
        ));
    }
} 