<?php

namespace Example\Controller;

use Example\Response;

ErrorHandler::registerHandler();

class Basic
{
    public function __construct()
    {
        $this->auth();
    }

    protected function auth()
    {
    }
}

class ErrorHandler
{
    static public function registerHandler()
    {
        set_error_handler(__CLASS__ . '::errorHandler');
        set_exception_handler(__CLASS__ . '::exceptionHandler');
    }

    static public function errorHandler($code, $msg, $file, $line)
    {
        if (!error_reporting())
            return;

        throw new \ErrorException($msg, $code, 0, $file, $line);
    }

    /**
     * @param \ErrorException $e
     */
    static public function exceptionHandler($e)
    {
        $error = $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine() . PHP_EOL;
        $error .= $e->getTraceAsString();
        //if (defined('TEST'))
        echo $error;

        \Example\Data\Log::getInstance()->add($error);
    }
}

namespace Example;


class Response
{
    static function ok($data = array(), $tpl = '', $code = 0)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(array('c' => $code, 'd' => $data), \JSON_UNESCAPED_UNICODE);
        } else {
            if (!is_array($data))
                $data = array('c' => $code, 'd' => $data);

            (new \Parith\View\Basic())->assign($data)->render($tpl);
        }

        exit(0);
    }

    static function error($msg, $code)
    {
        self::ok($msg, 'error', $code);
    }
}