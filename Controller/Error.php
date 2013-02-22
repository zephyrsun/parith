<?php

/**
 * Error
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Controller;

class Error extends \Parith\Basic
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

    public function getText()
    {
        return \Parith\Exception::text($this->exception);
    }

    /**
     * @return void
     */
    public function index()
    {
        $text = $this->getText();

        $this->httpStatus($this->exception->getCode());
        $this->web($text);
    }

    /**
     * @param string $text
     * @return void
     */
    public function web($text = 'error')
    {
        echo '<pre>';
        $this->cli($text);
        echo '</pre>';
    }

    /**
     * @param string $text
     * @return void
     */
    public function cli($text = 'error')
    {
        echo PHP_EOL . $text . PHP_EOL . $this->exception->getTraceAsString() . PHP_EOL;
    }

    /**
     * @param int $code
     * @return bool
     */
    public function httpStatus($code)
    {
        return \Parith\Lib\Response::httpStatus($code);
    }
}