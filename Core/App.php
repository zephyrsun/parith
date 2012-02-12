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
    public static $tr_pairs = array(), $configs = array(), $options = array(), $is_cli = false, $file_ext = '.php', $config_dir;

    public function __construct($app_dir, $config_path = 'Config')
    {
        \define('APP_NS', basename($app_dir) . '\\');
        \define('APP_DIR', $app_dir);

        self::$config_dir = APP_NS . $config_path . DS;

        self::_init();
    }

    /**
     * @static
     * @param null|string $route
     * @param array $arr
     * @return mixed
     */
    public static function dispatch($route = null, array &$arr = array())
    {
        $r = Router::parseCA($route, $arr);
        return self::invoke($r[0], $r[1]);
    }

    /**
     * @param null|string $route
     * @return mixed
     */
    public function run($route = null)
    {
        return self::dispatch($route, $_GET);
    }

    /**
     * @param null|string $route
     * @return mixed
     */
    public function cgi($route = null)
    {
        return $this->run($route);
    }

    /**
     * @return mixed
     */
    public function cli()
    {
        $argv = $_SERVER['argv'];

        $method = isset($argv[1]) ? \explode('?', $argv[1]) : array('');

        # treated as $_GET
        isset($method[1]) and \parse_str($method[1], $_GET);

        # treated as $_POST
        isset($argv[2]) and \parse_str($argv[2], $_POST);

        self::$is_cli = true;
        return $this->run($method[0]);
    }

    /**
     * @static
     * @param $controller
     * @param $action
     * @return mixed
     */
    public static function invoke($controller, $action)
    {
        $class = self::getController($controller);
        return $class->$action();
    }

    /**
     * @static
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public static function getController($name)
    {
        $class = APP_NS . 'Controller\\' . $name;

        if (\class_exists($class))
            return new $class;

        throw new \Parith\Exception('Controller "' . $name . '" not found', 404);
    }

    /**
     * @static
     * @return array
     */
    private static function _init()
    {
        self::$tr_pairs = array(APP_NS => APP_DIR, 'Parith\\' => PARITH_DIR, '\\' => DS);

        # initial options
        self::$options = $options = self::option('App', array(), array('timezone' => 'UTC'));

        # timezone setup
        \date_default_timezone_set($options['timezone']);

        # now time
        define('APP_TIME', \time());

        # Parith Exception handler
        \set_error_handler('\Parith\Exception::error');
        \set_exception_handler('\Parith\Exception::handler');

        return $options;
    }

    /**
     * @static
     * @param $name
     * @param array $before
     * @param array $after
     * @return array
     */
    public static function option($name, array $before = array(), array $after = array())
    {
        return $before + self::loadConfig($name) + $after;
    }

    /**
     * @static
     * @param $name
     * @return mixed
     */
    public static function loadConfig($name)
    {
        $cfg = &self::$configs;
        isset($cfg[$name]) or $cfg[$name] = self::import(self::$config_dir . $name . self::$file_ext, array(), false);
        return $cfg[$name];
    }

    /**
     * @static
     * @param $name
     * @param array $default
     * @param bool $log
     * @return array|mixed
     */
    public static function import($name, $default = array(), $log = true)
    {
        $name = self::parseName($name);
        if (\is_file($name))
            return include $name;

        $log and  Monitor::addLog('File "' . $name . '" not found');
        return $default;
    }

    /**
     * @static
     * @param $name
     * @return string
     */
    public static function parseName($name)
    {
        return \strtr($name, self::$tr_pairs);
    }

    /**
     * @static
     * @param $class
     * @return array|mixed
     */
    public static function autoload($class)
    {
        return self::import($class . self::$file_ext);
    }
}

/**
 * Router
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
class Router
{
    public $options = array(
        'delimiter' => '/',
        'rules' => array(),
        // 'default' could be array('c' => 'Home', 'a' => 'index'), it's up to you, but 'controller' must before 'action'
        'default' => array('controller' => 'Index', 'action' => 'index'),
    );

    private $_arr = array(), $_ca = array();

    /**
     * @param array $options
     * @return Router
     */
    public function __construct(array $options = array())
    {
        $this->options = App::option('Router', $options, $this->options);
    }

    /**
     * @return array
     */
    public function getCA()
    {
        return $this->_ca;
    }

    /**
     * @static
     * @param null|string $route
     * @param array $arr
     * @return array
     */
    public static function parseCA($route = null, array &$arr = array())
    {
        $r = new Router();
        $r->parse($route, $arr);
        return $r->getCA();
    }

    /**
     * @param null|string $key
     * @return array|mixed
     */
    public function getParams($key = null)
    {
        if ($key === null)
            return $this->_arr;

        return \Parith\Arr::get($this->_arr, $key);
    }

    public static function getPathInfo($arr)
    {
        return isset($arr['PATH_INFO']) ? \ltrim($arr['PATH_INFO'], '/') : null;
    }

    /**
     * @param null|string $route
     * @param array $arr
     * @return array
     */
    public function parse($route = null, array &$arr = array())
    {
        $options = $this->options;

        # parse route
        $route === null and $route = self::getPathInfo($arr);
        if ($route) {
            $this->parsePath($route, $options);
        }
        else {
            foreach ($options['default'] as $key => $val)
                $this->_ca[] = isset($arr[$key]) ? $arr[$key] : $val;

            $this->_ca[0] = \ucfirst($this->_ca[0]);
        }

        return $this->_arr = $arr;
    }

    /**
     * @param $uri
     * @param $options
     * @return array
     */
    public function parsePath($uri, $options)
    {
        foreach ($options['rules'] as $key => $val)
        {
            $r = \preg_replace('/^' . $key . '$/i', $val, $uri);
            if ($key !== $r) {
                $uri = $r;
                break;
            }
        }

        $arr = \explode($options['delimiter'], $uri);

        // controller
        $this->_ca[0] = \ucfirst($arr[0]);

        // action
        $this->_ca[1] = \next($arr) or $this->_ca[1] = \end($options['default']);

        return $arr;
    }
}