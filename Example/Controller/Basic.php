<?php

namespace Example\Controller {

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
            \respError('Query error', 402);
        }
    }
}

namespace {

    use Parith\View\View;

    function respOk($data = [], $tpl = '', $code = 0)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['c' => $code, 'd' => $data], \JSON_UNESCAPED_UNICODE);
        } else {
            if (!is_array($data))
                $data = ['c' => $code, 'd' => $data];

            (new View())->assign($data)->render($tpl);
        }

        exit;
    }

    function respError($msg, $code)
    {
        respOk($msg, 'error', $code);
    }
}

