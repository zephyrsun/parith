<?php

namespace Example\Data;

use Parith\DataSource\PDO;

/**
 * see doc/example.sql
 */
class Log extends PDO
{
    public $cfg_key = 'database_1';

    public $table_name = 'logs';

    public function __construct()
    {
        $this->dial($this->cfg_key);
    }

    public function add($data, $code = 0)
    {
        if (is_array($data))
            $data = json_encode($data, \JSON_UNESCAPED_UNICODE);

        return $this->insert([
            'get' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'],
            'post' => $_POST ? http_build_query($_POST) : file_get_contents('php://input'),
            'data' => $data,
            'code' => $code,
            'srv_ip' => &$_SERVER['SERVER_ADDR'],
            'client_ip' => \Parith\Lib\Request::getClientIp(),
            'time' => \date("Y-m-d H:i:s", \APP_TS), //time
        ]);
    }

    public function getList()
    {
        $p = $this->paginate(20);

        print_r($p->getTotal() . "\n");
        print_r($p->getPageTotal() . "\n");

        foreach ($p as $row) {
            print_r($row);
        }

        print_r($p->render());

        return $p;
    }
} 