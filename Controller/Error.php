<?php

/**
 * Error
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2011 Zephyr Sun
 * @license http://www.parith.org/license
 * @version 0.3
 * @link http://www.parith.org/
 */

namespace Parith\Controller;

class Error extends Controller
{
    public $exception;

    /**
     * @param \Exception $e
     * @return Error
     */
    public function __construct(\Exception $e)
    {
        $this->exception = $e;
    }

    /**
     * @param string $message
     * @return void
     */
    public function index($message = 'error')
    {
        if (\Parith\App::$is_cli) {
            $this->cli($message);
        }
        else {
            static::httpStatus($this->exception->getCode());
            $this->web($message);
        }
    }

    /**
     * @param string $message
     * @return void
     */
    protected function web($message = 'error')
    {
        echo '<pre>';
        $this->cli($message);
        echo '</pre>';
    }

    /**
     * @param string $message
     * @return void
     */
    protected function cli($message = 'error')
    {
        echo PHP_EOL . $message . PHP_EOL . $this->exception->getTraceAsString() . PHP_EOL;
    }

    /**
     * @param int $code
     * @return bool
     */
    protected static function httpStatus($code)
    {
        return \Parith\Lib\Response::httpStatus($code);
    }
}