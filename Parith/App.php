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

\spl_autoload_register('\Parith\App::import');

class App
{
    public static $options = array(
        'namespace' => 'App',
        'debug' => true,
        'logger' => '\Parith\Log',
    );

    private static $_instances = array();

    public function __construct(array $options = array())
    {
        self::setOption($options);
    }

    public static function setOption(array $options)
    {
        self::$options = $options + self::$options;

        self::$options;
    }

    public static function getOption($key)
    {
        $option = & self::$options[$key] or $option = array();

        return $option;
    }

    /**
     * run()
     */
    public function run()
    {
        $this->_run($this->getPathInfo());
    }

    /**
     *  php cmd.php "?c=index&a=cli" "key1=value1&key2=value2"
     */
    public function cmd()
    {
        $argv = $_SERVER['argv'];

        if (!isset($argv[1]))
            throw new \Exception('Please input Controller/Action');

        // treated as $_POST
        if (isset($argv[2]))
            \parse_str($argv[2], $_POST);

        $argv = \explode('?', $argv[1]);

        // treated as $_GET
        if (isset($argv[1]))
            \parse_str($argv[1], $_GET);

        $this->_run($argv[0]);
    }

    /**
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    private function _run($url)
    {
        // now time
        define('APP_TS', \time());

        //define APP_DIR
        $namespace = self::getOption('namespace');
        define('APP_DIR', BASE_DIR . $namespace . DIRECTORY_SEPARATOR);

        // timezone setup
        //\date_default_timezone_set(self::getOption('timezone'));

        //\set_error_handler('\Parith\App::errorHandler');
        //\set_exception_handler('\Parith\App::exceptionHandler');

        $query = Router::parse($url);

        $class = $namespace . '\\Controller\\' . \ucfirst($query[0]);

        if (self::import($class)) {
            $object = new $class;
            return $object->{$query[1]}();
        }

        throw new \Exception('Controller "' . $class . '" not found');
    }

    public static function errorHandler($code, $message, $file, $line)
    {
        if (!($code & error_reporting()))
            return;

        throw new \ErrorException($message, $code, 0, $file, $line);
    }

    public static function exceptionHandler(\Exception $e)
    {
        $log = new self::$options['logger']();
        $log->writeException($e);
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
     * @static
     * @param $name
     * @return bool|mixed
     */
    public static function import($name)
    {
        $name = BASE_DIR . \str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
        if (\is_file($name))
            return include $name;

        return false;
    }

    /**
     * @static
     * @param $class
     * @param $args
     * @param string $key
     * @return mixed
     */
    public static function getInstance($class, $args = array(), $key = '')
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
        'index' => array('controller', 'action'),
        'default' => array('Index', 'index'),
    );

    private static $_query = array();

    /**
     * @param string $url
     * @param array $options
     * @return array
     */
    public static function parse($url = '', array $options = array())
    {
        $options = $options + App::getOption('router') + self::$options;

        if ($url) {
            return self::$_query = self::parseURL(trim($url, '/'), $options) + $options['default'];
        }

        $c = & $_GET[$options['index'][0]] or $c = $options['default'][0];
        $a = & $_GET[$options['index'][1]] or $a = $options['default'][1];

        return self::$_query = array($c, $a);
    }

    public static function getQuery()
    {
        return self::$_query;
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
    public function __construct()
    {
    }

    public function writeException(\Exception $e)
    {
        $this->write(sprintf('%s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    }

    /**
     * write
     */
    public function write($message)
    {
        if (App::getOption('debug')) {
            echo $message;
        } else {
            $message = date(DATE_RFC2822, APP_TS) . ' ' . $message . PHP_EOL;

            $file = APP_DIR . 'log' . DIRECTORY_SEPARATOR . date('Y-m-d', APP_TS) . '.log';

            \error_log($message, 3, $file);
        }
    }
}