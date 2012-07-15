<?php

/**
 * App
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
\spl_autoload_register('\Parith\App::autoload');

class App
{
    public static
        $replace_src = array(),
        $replace_dst = array(),
        $is_cli = false,
        $app_action,
        $options = array(
        'app_dir' => 'App',
        'app' => array(),
    );

    /**
     * @static
     * @param string $uri
     * @return mixed
     */
    public static function run($uri = '')
    {
        self::init();

        $r = new Router();

        return self::invoke(self::$app_action = $r->parse($uri, $_GET));
    }

    /**
     * @static
     * @return mixed
     * @throws Exception
     */
    public static function cli()
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
        return self::run($argv[0]);
    }

    /**
     * @static
     *
     */
    public static function init()
    {
        $path = realpath(self::$options['app_dir']);
        \define('APP_NS', basename($path) . '\\');
        \define('APP_DIR', $path . DIRECTORY_SEPARATOR);

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
        return self::getController($params[0])->$params[1]();
    }

    /**
     * @static
     * @param $name
     * @return bool|mixed
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
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */
class Router
{
    public $options = array(
        'delimiter' => '/',
        'rules' => array(),
        'default_keys' => array('controller', 'action'),
        'default_values' => array('Index', 'index'),
    );

    /**
     * @param array $options
     * @return Router
     */
    public function __construct(array $options = array())
    {
        $this->options = App::getOption('router', $options) + $this->options;
    }

    public static function getUri()
    {
        return isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);
    }

    /**
     * @param string $uri
     * @param array $arr
     * @return array
     */
    public function parse($uri = '', array &$arr = array())
    {
        $options = $this->options;

        // get route info
        $uri or $uri = self::getUri();

        if ($uri && $uri !== '/') {
            $arr = self::parseUri(trim($uri, '/'), $options) + $options['default_values'] + $arr;

            $c = $arr[0];
            $a = $arr[1];

            //unset($arr[0], $arr[1]);
        } else {
            $c = &$arr[$options['default_keys'][0]] or $c = $options['default_values'][0];
            $a = &$arr[$options['default_keys'][1]] or $a = $options['default_values'][1];
        }

        return array(\ucfirst($c), $a);
    }

    /**
     * @param $uri
     * @param $options
     * @return array
     */
    public static function parseUri($uri, $options)
    {
        foreach ($options['rules'] as $key => $val) {
            $r = \preg_replace('/^' . $key . '$/i', $val, $uri, -1, $n);
            if ($n) {
                $uri = $r;
                break;
            }
        }

        return \explode($options['delimiter'], $uri);
    }
}