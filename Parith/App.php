<?php

/**
 * App
 *
 * Notice:
 *      - Please use \date_default_timezone_set() to setup timezone in index.php by yourself, if need.
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith;

define('BASE_DIR', dirname(__DIR__) . \DIRECTORY_SEPARATOR);

// load core class
include __DIR__ . '/Common.php';
include __DIR__ . '/Controller/Basic.php';

\spl_autoload_register('\Parith\App::import');

class App
{
    static public $options = array('namespace' => 'App')
    , $query = array()
    , $_ins = array();

    public function __construct(array $options = array())
    {
        self::$options = $options + self::$options;
    }

    static public function getOption($key)
    {
        return self::$options[$key] ?? array();
    }

    /**
     * /path/to/php shell.php "/index/cli?get1=value1&get2=value2" "post1=value1&post2=value2"
     */
    public function shell()
    {
        $argv = $_SERVER['argv'];

        if (!isset($argv[1]))
            throw new \Exception('Please input Controller/Action');

        // treated as $_POST
        if (isset($argv[2]))
            \parse_str($argv[2], $_POST);

        $argv = \explode('?', $argv[1]);

        // treated as $_GET
        if (isset($argv[1])) {
            $_SERVER['QUERY_STRING'] = $argv[1];
            \parse_str($argv[1], $_GET);
        }

        $_GET['URI'] = $argv[0];

        $this->run();
    }

    /**
     * @param string $uri Admin/index
     *
     * @return mixed
     * @throws \Exception
     */
    public function run()
    {
        // now time
        define('APP_TS', \time());

        //define APP_DIR
        $ns = self::$options['namespace'];
        define('APP_DIR', BASE_DIR . $ns . DIRECTORY_SEPARATOR);

        // timezone setup
        //\date_default_timezone_set(self::getOption('timezone'));

        //\set_error_handler('\Parith\App::errorHandler');
        //\set_exception_handler('\Parith\App::exceptionHandler');

        self::$query = $query = Router::parse($_GET['URI'] ?? '');

        $class = $ns . '\\Controller\\' . \ucfirst($query[0]);

        if (self::import($class)) {
            $object = new $class;
            return $object->{$query[1]}();
        }

        throw new \Exception('Controller "' . $class . '" not found');
    }

    /**
     * @return array
     */
    static public function getQuery()
    {
        return self::$query;
    }

    /*
    static public function errorHandler($code, $message, $file, $line)
    {
        if (!($code & error_reporting()))
            return;

        throw new \ErrorException($message, $code, 0, $file, $line);
    }
    */

    /**
     * @static
     *
     * @param $name
     *
     * @return bool|mixed
     */
    static public function import($name)
    {
        $name = BASE_DIR . \str_replace('\\', \DIRECTORY_SEPARATOR, $name) . '.php';
        if (\is_file($name))
            return include $name;

        return null;
    }

    /**
     * @static
     *
     * @param        $class
     * @param        $args
     * @param string $key
     *
     * @return mixed
     */
    static public function getInstance($class, $args = array(), $key = '')
    {
        if (!$key)
            $key = $class;

        $obj = &self::$_ins[$key];
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
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */
class Router
{
    static public $options = array(
        'delimiter' => '/',
        'index' => array('c', 'a'), //array('controller', 'action'),
        'default' => array('Index', 'index'),
    );

    /**
     * @param string $uri
     *
     * @return array
     */
    static public function parse($uri = '')
    {
        $options = App::getOption('router') + self::$options;

        if ($uri) {
            $arr = \explode($options['delimiter'], \trim($uri, '/'));
            if (count($arr) == 1)
                $arr = array($options['default'][0], $arr[0]);

            return $arr;
        }

        $c = $_GET[$options['index'][0]] ?? $options['default'][0];
        $a = $_GET[$options['index'][1]] ?? $options['default'][1];

        return array($c, $a);
    }
}