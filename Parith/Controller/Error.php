<?php

/**
 * Controller
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Controller;

class Error
{
    public function registerHandler()
    {
        $class = get_called_class();
        set_error_handler($class . '::errorHandler');
        set_exception_handler($class . '::exceptionHandler');
    }

    static public function errorHandler($code, $msg, $file, $line)
    {
        if ($code & error_reporting() == 0)
            return;

        throw new \ErrorException($msg, $code, 0, $file, $line);
    }

    /**
     * @param \ErrorException $e
     */
    static public function exceptionHandler($e)
    {
        $error = $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine();

        echo $error;
        echo PHP_EOL;
    }

    /**
     * @param $name
     * @param $args
     *
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        echo "'$name' not found";
        // throw new \Exception('Action "' . $name . '" not found');
    }
}