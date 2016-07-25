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

\spl_autoload_register('\Parith\import');

/**
 * @param $name
 * @return mixed
 */
function import($name)
{
    $name = BASE_DIR . \str_replace('\\', \DIRECTORY_SEPARATOR, $name) . '.php';
    if (\is_file($name))
        return include $name;

    return null;
}

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
 * @copyright 2009-2016 Zephyr Sun
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
            $arr = \explode($options['delimiter'], \trim($uri, '/')) + $options['default'];
            //if (count($arr) == 1)
            //    $arr = array($options['default'][0], $arr[0]);

            return $arr;
        }

        $c = &$_GET[$options['index'][0]] or $c = $options['default'][0];
        $a = &$_GET[$options['index'][1]] or $a = $options['default'][1];

        return array($c, $a);
    }
}


abstract class Result implements \Iterator, \ArrayAccess, \Countable
{
    protected $_rs = array();

    /**
     * @param $key
     * @param $val
     *
     * @return Result
     */
    public function __set($key, $val)
    {
        $this->_rs[$key] = $val;

        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->_rs[$key];
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_rs[$key]);
    }

    /**
     * @param $key
     *
     * @return Result
     */
    public function __unset($key)
    {
        unset($this->_rs[$key]);

        return $this;
    }

    /**
     * @param       $key
     * @param mixed $val
     *
     * @return Array
     */
    public function resultSet($key, $val = null)
    {
        if (\is_array($key))
            $this->_rs = $key + $this->_rs;
        elseif ($key)
            $this->__set($key, $val);

        return $this->_rs;
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function resultGet($key = null)
    {
        if ($key === null)
            return $this->_rs;

        return $this->__get($key);
    }

    /**
     * @param $key
     *
     * @return Result
     */
    public function resultDelete($key)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $v)
                $this->__unset($k);
        } else
            $this->__unset($key);

        return $this;
    }

    /**
     * @return Result
     */
    public function resultFlush()
    {
        $this->_rs = array();

        return $this;
    }

    // Iterator Methods

    /**
     * @return mixed
     */
    public function rewind()
    {
        return \reset($this->_rs);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return \current($this->_rs);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return \key($this->_rs);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return \next($this->_rs);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    // Countable Methods

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->_rs);
    }

    // ArrayAccess Methods

    /**
     * @param $key
     * @param $val
     *
     * @return Result
     */
    public function offsetSet($key, $val)
    {
        return $this->__set($key, $val);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    /**
     * @param $key
     *
     * @return Result
     */
    public function offsetUnset($key)
    {
        return $this->__unset($key);
    }

    /**
     * @return object
     */
    static public function getInstance()
    {
        return App::getInstance(\get_called_class(), \func_get_args());
    }
}