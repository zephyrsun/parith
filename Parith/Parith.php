<?php

/**
 * Parith
 *
 * Notice:
 *      - Please use \date_default_timezone_set() to setup timezone.
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace {

    define('BASE_DIR', dirname(__DIR__) . \DIRECTORY_SEPARATOR);

    class Parith
    {
        static public $env = [
            'namespace' => 'App',
            'error_class' => '\Parith\Error',
            'route' => ['Index', 'index'],
        ], $_ins = [];

        public function __construct(array $options = [])
        {
            self::$env = $options + self::$env;

            //recommend set in php.ini
            //\date_default_timezone_set(self::getOption('timezone'));

            // now time
            define('APP_TS', \time());

            //define APP_DIR
            define('APP_DIR', BASE_DIR . self::$env['namespace'] . DIRECTORY_SEPARATOR);
        }

        static public function getEnv($key)
        {
            return isset(self::$env[$key]) ? self::$env[$key] : [];
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
         *
         * http://domain/
         * http://domain/user/home
         * http://domain/Admin/user/edit (need create 'Admin' directory)
         *
         * @return mixed
         * @throws Exception
         */
        public function web()
        {
            set_error_handler('\Parith\Error::errorHandler');
            set_exception_handler('\Parith\Error::exceptionHandler');

            $r = &self::$env['route'];
            $u = &$_GET['URI'];
            if ($u = $u ? \trim($u, '/') : '')
                $r = \explode('/', $u) + $r;

            if (\import($class = self::$env['namespace'] . '\\Controller\\' . \ucfirst($r[0]))) {
                return (new $class())->{$r[1]}();
            }

            // echo $class . ' not found.';
            throw new \Exception($class . ' not found.', 404);
        }

        static function pushRoute($route)
        {
            self::$env['route'][] = $route;
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

    \spl_autoload_register('\import');
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
}

namespace Parith {

    class Controller
    {
        public function __call($name, $args)
        {
            throw new \Exception($name . ' not found.', 404);
        }

        /**
         * list($p1, $p2) = $this->routeParams(2);
         *
         * @param int $num
         * @return array|string
         */
        static public function routeParams($num = 0)
        {
            $i = 2;
            $r = \Parith::getEnv('route');

            if ($num > 0) {
                $num += $i;
                $ret = [];
                for (; $i < $num; $i++)
                    $ret[] = &$r[$i];
            } else {
                $ret = &$r[$i];
            }

            return $ret;
        }
    }

    class Error
    {
        /**
         * @param $code
         * @param $msg
         * @param $file
         * @param $line
         * @throws \ErrorException
         */
        static public function errorHandler($code, $msg, $file, $line)
        {
            if ($code & error_reporting() == 0)
                return;

            throw new \ErrorException($msg, $code, 0, $file, $line);
        }

        /**
         * @param $e
         */
        static public function exceptionHandler($e)
        {
            $class = \Parith::getEnv('error_class');

            $str = $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine() . PHP_EOL;
            $str .= 'Trace: ' . PHP_EOL . $e->getTraceAsString();

            (new $class())->render($e, "<pre>$str</pre>");
        }

        /**
         * @param \Throwable $e
         * @param $str
         */
        public function render($e, $str)
        {
            echo "<pre>$str</pre>";
        }
    }

    class Result implements \Iterator, \ArrayAccess, \Countable
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
            return \Parith::getInstance(static::class, \func_get_args());
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
        public function set($key, $value = null)
        {
            if (\is_array($key))
                $this->merge($key);
            elseif ($key)
                $this->__set($key, $value);

            return $this;
        }

        public function merge($arr)
        {
            $this->__ = $arr + $this->__;

            return $this;
        }

        /**
         * @param $key
         * @return mixed
         */
        public function get($key)
        {
            return $this->__get($key);
        }

        /**
         * @return array
         */
        public function &toArray()
        {
            return $this->__;
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
}