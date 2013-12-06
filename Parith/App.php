<?php

/**
 * App
 *
 * Notice:
 *      - Please use \date_default_timezone_set() to setup timezone in index.php by yourself, if need.
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith;

define('BASE_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// load core class
include __DIR__ . '/Common.php';
include __DIR__ . '/Controller/Basic.php';

class App
{
    public static $options = array('namespace' => 'App', 'debug' => true);

    private static $_instances = array();

    public function __construct(array $options = array())
    {
        self::setOption($options);
    }

    public static function setOption(array $options)
    {
        self::$options = $options + self::$options;

        return self::$options;
    }

    public static function getOption($key)
    {
        $option = & self::$options[$key];

        return $option;
    }

    public static function registerAutoloader()
    {
        \spl_autoload_register('\Parith\App::import');
    }

    /**
     * run()
     */
    public function run()
    {
        $this->_run($this->getPathInfo());
    }

    /**
     * cli()
     */
    public function cli()
    {
        $argv = $_SERVER['argv'];

        if (!isset($argv[1]))
            Log::write('Please input Controller/Action');

        // treated as $_POST
        if (isset($argv[2]))
            \parse_str($argv[2], $_POST);

        $argv = \explode('?', $argv[1]);

        // treated as $_GET
        if (isset($argv[1]))
            \parse_str($argv[1], $_GET);

        $this->_run($argv[0]);
    }

    private function _run($url)
    {
        // now time
        define('APP_TS', \time());

        define('APP_NS', self::getOption('namespace'));

        // timezone setup
        //\date_default_timezone_set(self::$options['app']['timezone']);

        \set_error_handler('\Parith\App::errorHandler');
        \set_exception_handler('\Parith\App::exceptionHandler');

        $query = Router::parse($url);

        $this->getController($query[0])->{$query[1]}();
    }

    public static function errorHandler($code, $message, $file, $line)
    {
        if (!($code & \error_reporting()))
            return;

        throw new \ErrorException($message, $code, 0, $file, $line);
    }

    public static function exceptionHandler(\Exception $e)
    {
        $text = \sprintf('[%s] %s: %d', $e->getMessage(), $e->getFile(), $e->getLine());

        Log::write($text);
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        if (isset($_GET['URI']))
            return $_GET['URI'];
    }

    /**
     * @param $name
     * @return bool|object
     * @throws Exception
     */
    public function getController($name)
    {
        $class = APP_NS . '\\Controller\\' . $name;

        if (\class_exists($class))
            return new $class;

        Log::write('Controller "' . $name . '" not found');

        return false;
    }

    /**
     * @static
     * @param $name
     * @param bool $throw
     * @return bool|mixed
     * @throws Exception
     */
    public static function import($name, $throw = true)
    {
        $name = BASE_DIR . \str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php'; //\strtr($name, self::$name_pair);
        if (\is_file($name))
            return include $name;

        log::write('File "' . $name . '" not found');

        return false;
    }

    /**
     * @static
     * @param $class
     * @param $args
     * @param null $key
     * @return mixed
     */
    public static function getInstance($class, $args = array(), $key = null)
    {
        $key or $key = $class;
        $obj = & self::$_instances[$key];
        if ($obj)
            return $obj;

        switch (count($args)) {
            case 1:
                return $obj = new $class($args[0]);
            case 2:
                return $obj = new $class($args[0], $args[1]);
            case 3:
                return $obj = new $class($args[0], $args[1], $args[2]);
            default:
                return $obj = new $class();
        }
    }
}

/**
 * Router
 *
 * Parith :: a compact PHP framework
 *
 * $options e.g.:
 *  array(
 *      'delimiter' => '/',
 *      'rules' => array('\d+' => 'Article/view/${0}'),
 *  );
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */
class Router
{
    public static $options = array(
        'delimiter' => '/',
        'rules' => array(),
        'accept' => array('controller', 'action'),
        'default' => array('Index', 'index'),
    );

    /**
     * @param string $url
     * @param array $options
     * @return array
     */
    public static function parse($url = '', array $options = array())
    {
        $options = $options + App::getOption('router') + self::$options;

        if ($url) {
            //$url = explode('?', $url, 2);
            $arr = self::parseURL(trim($url, '/'), $options) + $options['default'];

            $arr[0] = \ucfirst($arr[0]);

            return $arr;
        }

        $arr = $_GET;

        $c = & $arr[$options['accept'][0]] or $c = $options['default'][0];
        $a = & $arr[$options['accept'][1]] or $a = $options['default'][1];

        return array(\ucfirst($c), $a);
    }

    /**
     * @param $url
     * @param $options
     * @return array
     */
    public static function parseURL($url, $options)
    {
        foreach ($options['rules'] as $key => $val) {
            $r = \preg_replace('/^' . $key . '$/i', $val, $url, -1, $n);
            if ($n) {
                $url = $r;
                break;
            }
        }

        return \explode($options['delimiter'], $url);
    }
}


class Log
{
    /**
     * @param $message
     */
    public static function write($message)
    {
        if (App::getOption('debug')) {
            echo $message;
        } else {
            $message = \date(\DATE_RFC2822, APP_TS) . ' ' . $message . PHP_EOL;

            $file = BASE_DIR . 'log' . DIRECTORY_SEPARATOR . \date('Y-m-d', APP_TS) . '.log';

            \error_log($message, 3, $file);
        }
    }
}