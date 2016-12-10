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
    static public $options = [
        'namespace' => 'App',
        'error_class' => Error::class
    ], $_ins = [];

    public function __construct(array $options = [])
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
        return isset(self::$options[$key]) ? self::$options[$key] : [];
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
        set_error_handler('\Parith\Error::errorHandler');
        set_exception_handler('\Parith\Error::exceptionHandler');

        Router::parse(isset($_GET['URI']) ? $_GET['URI'] : '', $class, $action);

        if (import($class = self::$options['namespace'] . '\\Controller\\' . \ucfirst($class))) {
            $object = new $class();
            return $object->{$action}();
        }

        //return (new self::$options['error_class'])->__call($class, $action);
    }

    /**
     * @param $class
     * @param array $args
     * @param string $key
     * @return object
     */
    static public function getInstance($class, $args = [], $key = '')
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
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */
class Router
{
    static public $options = [
        'delimiter' => '/',
        'index' => ['c', 'a'], //['controller', 'action'],
        'default' => ['Index', 'index'],
    ];

    /**
     * @param string $uri
     * @param $class
     * @param $action
     * @return array
     */
    static public function parse($uri = '', &$class, &$action)
    {
        $options = App::getOption('router') + self::$options;

        if ($uri) {
            list($class, $action) = \explode($options['delimiter'], \trim($uri, '/')) + $options['default'];
            return;
        }

        $c = &$_GET[$options['index'][0]];
        $a = &$_GET[$options['index'][1]];

        $class = $c or $class = $options['default'][0];
        $action = $a or $action = $options['default'][1];
    }
}

class Error
{
    static public function errorHandler($code, $msg, $file, $line)
    {
        if ($code & error_reporting() == 0)
            return;

        throw new \ErrorException($msg, $code, 0, $file, $line);
    }

    static public function exceptionHandler(\Throwable $e)
    {
        $class = App::getOption('error_class');

        (new $class())->renderError($e);
    }

    static public function render(\Throwable $e)
    {
        $error = $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine() . PHP_EOL;
        $error .= $e->getTraceAsString();

        echo "<pre>$error</pre>";

    }
}

abstract class Result implements \Iterator, \ArrayAccess, \Countable
{
    protected $__ = [];

    public $options = [];

    public function setOptions($options)
    {
        $this->options = $options + $this->options;
    }

    /**
     * @return object
     */
    static public function getInstance()
    {
        return App::getInstance(static::class, \func_get_args());
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function __set($key, $value = null)
    {
        $this->__[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->__[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->__[$key]);
    }

    /**
     * @param $key
     * @return $this
     */
    public function __unset($key)
    {
        unset($this->__[$key]);

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (\is_array($key))
            $this->__ = $key + $this->__;
        elseif ($key)
            $this->__set($key, $value);

        return $this;
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function get($key = null)
    {
        if ($key === null)
            return $this->__;

        return $this->__get($key);
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function delete($key)
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
    public function flush()
    {
        $this->__ = [];

        return $this;
    }

    // Iterator Methods

    /**
     * @return mixed
     */
    public function rewind()
    {
        return \reset($this->__);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return \current($this->__);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return \key($this->__);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return \next($this->__);
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
        return \count($this->__);
    }

    // ArrayAccess Methods

    /**
     * @param $key
     * @param $value
     * @return Result
     */
    public function offsetSet($key, $value)
    {
        return $this->__set($key, $value);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    /**
     * @param mixed $key
     * @return Result
     */
    public function offsetUnset($key)
    {
        return $this->__unset($key);
    }
}