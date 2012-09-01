<?php

/**
 * Monitor
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith;

class Monitor
{
    public static $log = array();
    private static $_status = array();

    /**
     * @param string $name
     * @return array
     */
    public static function mark($name)
    {
        return self::$_status[$name] = array('time' => \microtime(true), 'mem' => \memory_get_usage(), 'peak' => \memory_get_peak_usage());
    }

    /**
     * @param mixed $start
     * @param mixed $end
     * @param string $type time, mem or peak
     * @param int $decimal
     * @return float
     */
    public static function status($start = null, $end = null, $type = 'time', $decimal = 5)
    {
        if (\count(self::$_status) < 2)
            return false;

        $start = isset(self::$_status[$start]) ? self::$_status[$start] : \reset(self::$_status);
        $end = isset(self::$_status[$end]) ? self::$_status[$end] : \end(self::$_status);

        return \round($end[$type] - $start[$type], $decimal); #number_format
    }

    /**
     * @param string $message
     * @param int $type
     * @return array
     */
    public static function addLog($message, $type = 1024)
    {
        return self::$log[] = \date(\DATE_RFC2822, APP_TIME) . ' ' . $message;
    }

    /**
     * @return array
     */
    public static function getLog()
    {
        return self::$log;
    }

    /**
     * @param string $file
     * @return mixed;
     */
    public static function writeLog($file = null)
    {
        if (self::$log === array())
            return false;

        $file or $file = APP_DIR . 'Log' . \DIRECTORY_SEPARATOR . \date('Y-m-d', APP_TIME) . '.log';

        $ret = \error_log(\implode(PHP_EOL, self::$log), 3, $file);

        self::$log = array();

        return $ret ? $file : false;
    }

}

/**
 * Exception
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */
class Exception extends \Exception
{
    public static $php_errors = array(
        \E_ERROR => 'Error',
        \E_WARNING => 'Warning',
        \E_PARSE => 'Parse Error',
        \E_NOTICE => 'Notice',
        \E_CORE_ERROR => 'Core Error', // since PHP 4
        \E_CORE_WARNING => 'Core Warning', // since PHP 4
        \E_COMPILE_ERROR => 'Compile Error', // since PHP 4
        \E_COMPILE_WARNING => 'Compile Warning', // since PHP 4
        \E_USER_ERROR => 'User Error', // since PHP 4
        \E_USER_WARNING => 'User Warning', // since PHP 4Parse Error
        \E_USER_NOTICE => 'User Notice', // since PHP 4
        \E_STRICT => 'Strict Notice', // since PHP 5
        \E_RECOVERABLE_ERROR => 'Recoverable Error', // since PHP 5.2.0
        \E_DEPRECATED => 'Deprecated', // Since PHP 5.3.0
        \E_USER_DEPRECATED => 'User Deprecated', // Since PHP 5.3.0
    );

    /**
     * @param string $message
     * @param int $code
     * @return \Parith\Exception
     */
    public function __construct($message, $code = 0)
    {
        $this->message = $message;
        $this->code = $code ? $code : \E_ERROR;
    }

    /**
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public static function error($code, $message, $file, $line)
    {
        if (\error_reporting())
            throw new \ErrorException($message, $code, 0, $file, $line);

        return true;
    }

    /**
     * @param \Exception $e
     * @return void
     */
    public static function handler(\Exception $e)
    {
        try {
            $class = APP_NS . 'Controller\Error';
            $handler = \class_exists($class) ? new $class($e) : new \Parith\Controller\Error($e);
            self::log($e);
            $handler->index();
        }
        catch (\Exception $e) {
            self::log($e);
            print_r(\Parith\Monitor::getLog());
            exit(1);
        }
    }

    /**
     * @param int $code
     * @return mixed
     */
    public static function getCodeValue($code)
    {
        return isset(self::$php_errors[$code]) ? self::$php_errors[$code] : $code;
    }

    /**
     * @param \Exception $e
     * @return string
     */
    public static function log($e)
    {
        $message = self::text($e);
        Monitor::addLog($message);
        return $message;
    }

    /**
     * @param \Exception $e
     * @param string $format
     * @return string
     */
    public static function text(\Exception $e, $format = '[%s] [%s] %s: %d')
    {
        return \sprintf($format, self::getCodeValue($e->getCode()), $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::text($this);
    }
}