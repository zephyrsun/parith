<?php

namespace Example\Controller;

use Example\Response;

class Basic
{
    public function __construct()
    {
        $this->auth();
    }

    protected function auth()
    {
    }

    public function __call($val, $args)
    {
        Response::error('Query error', 402);
    }
}

namespace Example;


class Response
{
    static function ok($data = [], $tpl = '', $code = 0)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['c' => $code, 'd' => $data], \JSON_UNESCAPED_UNICODE);
        } else {
            if (!is_array($data))
                $data = ['c' => $code, 'd' => $data];

            (new \Parith\View\Basic())->assign($data)->render($tpl);
        }

        exit(0);
    }

    static function error($msg, $code)
    {
        self::ok($msg, 'error', $code);
    }
}