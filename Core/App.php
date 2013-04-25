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
\spl_autoload_register('\Parith\App::autoload');

class App
{
    public static $replace_src = array()
    , $replace_dst = array()
    , $is_cli = false
    , $query = array()
    , $options = array();

    /**
     * @param $app_dir
     * @param array $options
     */
    public static function run($app_dir, array $options = array())
    {
        self::init($app_dir, $options);

        self::invoke(Router::parse(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF'])));
    }

    /**
     * @param $app_dir
     * @param array $options
     * @throws Exception
     */
    public static function cli($app_dir, array $options = array())
    {
        $argv = $_SERVER['argv'];

        if (!isset($argv[1]))
            throw new \Parith\Exception('Please input Controller/Action');

        // treated as $_POST
        if (isset($argv[2]))
            \parse_str($argv[2], $_POST);

        $argv = \explode('?', $argv[1]);

        // treated as $_GET
        if (isset($argv[1]))
            \parse_str($argv[1], $_GET);

        self::$is_cli = true;

        self::init($app_dir, $options);

        self::invoke(Router::parse($argv[0]));
    }

    /**
     * @param $app_dir
     * @param array $options
     */
    public static function init($app_dir, array $options = array())
    {
        self::setOption($options);

        \define('APP_DIR', $app_dir . DIRECTORY_SEPARATOR);
        \define('APP_NS', basename(APP_DIR) . '\\');
        // now time
        define('APP_TS', \time());

        self::$replace_src = array(APP_NS, 'Parith\\', '\\');
        self::$replace_dst = array(APP_DIR, PARITH_DIR, DIRECTORY_SEPARATOR);

        // timezone setup
        //\date_default_timezone_set(self::$options['app']['timezone']);

        // Parith Exception handler
        \set_error_handler('\Parith\Exception::error');
        \set_exception_handler('\Parith\Exception::handler');
    }

    public static function setOption($key, $val = null)
    {
        if (\is_array($key))
            self::$options = $key + self::$options;
        elseif ($key)
            self::$options[$key] = $val;

        return self::$options;
    }

    public static function getOption($key, array $options = array())
    {
        if (isset(self::$options[$key]))
            return $options + self::$options[$key];

        return $options;
    }

    /**
     * @static
     * @param $params
     * @return mixed
     */
    public static function invoke($params)
    {
        self::$query = $params;
        return self::getController($params[0])->$params[1]();
    }

    /**
     * @param $name
     * @return bool|object
     * @throws Exception
     */
    public static function getController($name)
    {
        $class = APP_NS . 'Controller\\' . $name;

        if (\class_exists($class))
            return new $class;

        throw new \Parith\Exception('Controller "' . $name . '" not found', 404);

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
        $name = self::parseName($name);
        if (\is_file($name))
            return include $name;

        if ($throw)
            throw new \Parith\Exception('File "' . $name . '" is not exists');

        return false;
    }

    /**
     * @static
     * @param $name
     * @return string
     */
    public static function parseName($name)
    {
        return str_replace(self::$replace_src, self::$replace_dst, $name);
        // return \strtr($name, self::$tr_pairs);
    }

    /**
     * @static
     * @param $class
     * @return array|mixed
     */
    public static function autoload($class)
    {
        return self::import($class . '.php', false);
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
        $options = App::getOption('router', $options) + self::$options;

        if ($url) {
            $url = explode('?', $url, 2);
            $arr = self::parseURL(trim($url[0], '/'), $options) + $options['default']; // + $arr

            //$c = $arr[0];
            //  $a = $arr[1];

            //unset($arr[0], $arr[1]);

            $arr[0] = \ucfirst($arr[0]);

            return $arr;
        }

        $arr = $_GET;

        $c = &$arr[$options['accept'][0]] or $c = $options['default'][0];
        $a = &$arr[$options['accept'][1]] or $a = $options['default'][1];

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