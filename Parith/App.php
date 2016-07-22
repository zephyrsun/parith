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
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith;

define('BASE_DIR', dirname(__DIR__) . \DIRECTORY_SEPARATOR);

// load core class
include __DIR__ . '/Common.php';

\spl_autoload_register('\Parith\import');

class App
{
    static public $options = array('namespace' => 'App', 'error_class' => '\\Parith\\Controller\\Error')
    , $query = array()
    , $_ins = array();

    public function __construct(array $options = array())
    {
        self::$options = $options + self::$options;

        //recommend set in php.ini
        //\date_default_timezone_set(self::getOption('timezone'));

        // now time
        define('APP_TS', \time());

        //define APP_DIR
        define('APP_DIR', BASE_DIR . self::$options['namespace'] . DIRECTORY_SEPARATOR);
    }

    static public function getOption($key)
    {
        return isset(self::$options[$key]) ? self::$options[$key] : array();
    }

    /**
     * /path/to/php shell.php "/index/cli?get1=value1&get2=value2" "post1=value1&post2=value2"
     */
    public function run()
    {
        if (isset($_SERVER['SERVER_PORT']))
            return $this->web();

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

        return $this->web();
    }

    /**
     * @return mixed
     */
    public function web()
    {
        self::$query = $query = Router::parse(isset($_GET['URI']) ? $_GET['URI'] : '');

        $class = self::$options['namespace'] . '\\Controller\\' . \ucfirst($query[0]);

        if (import($class)) {
            $object = new $class;
            return $object->{$query[1]}();
        }

        $object = new self::$options['error_class'];
        return $object->{$query[0]}();
    }

    /**
     * @return array
     */
    static public function getQuery()
    {
        return self::$query;
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