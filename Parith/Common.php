<?php

/**
 * Object
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

abstract class Result implements \Iterator, \ArrayAccess, \Countable
{
    protected $_rs = array();

    /**
     * @param $key
     * @param $val
     * @return Result
     */
    public function __set($key, $val)
    {
        $this->_rs[$key] = $val;

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->_rs[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_rs[$key]);
    }

    /**
     * @param $key
     * @return Result
     */
    public function __unset($key)
    {
        unset($this->_rs[$key]);

        return $this;
    }

    /**
     * @param $key
     * @param mixed $val
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
     * @return Result
     */
    public function offsetSet($key, $val)
    {
        return $this->__set($key, $val);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    /**
     * @param $key
     * @return Result
     */
    public function offsetUnset($key)
    {
        return $this->__unset($key);
    }

    /**
     * @return object
     */
    public static function singleton()
    {
        return App::getInstance(\get_called_class(), \func_get_args());
    }
}

/**
 * Arr
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */
abstract class Arr
{
    /**
     * @static
     * @param array $arr
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array $arr, $key, $default = null)
    {
        if (isset($arr[$key]))
            return $arr[$key];

        return $default;
    }

    /**
     * @static
     * @param array $arr
     * @param string $key
     * @param mixed $val
     * @param string $delimiter
     * @return array
     */
    public static function pathSet(array &$arr, $key, $val, $delimiter = ',')
    {
        foreach (\explode($delimiter, $key) as $v)
            $arr = & $arr[$v] or $arr = array();

        return $arr = $val;
    }

    /**
     * @static
     * @param array $arr
     * @param string $key
     * @param string $delimiter
     * @return mixed
     */
    public static function pathGet(array &$arr, $key, $delimiter = ',')
    {
        foreach (\explode($delimiter, $key) as $val)
            if (!$arr = & $arr[$val])
                return false;

        return $arr;
    }

    /**
     * @static
     * @param array $arr
     * @param string $key
     * @return void
     */
    public static function pathDelete(array &$arr, $key)
    {
        $val = \explode('.', $key);
        $last = \end($val);

        foreach ($val as $v) {
            if ($v === $last)
                unset($arr[$v]);
            else
                $arr = & $arr[$v];
        }
    }

    /**
     * Arr::unshift($array, 'first element', 'element value')
     *
     * @static
     * @param array $arr
     * @param string $key
     * @param mixed $val
     * @return array
     */
    public static function unshift(array &$arr, $key, $val)
    {
        $arr = \array_reverse($arr, true);
        $arr[$key] = $val;
        return \array_reverse($arr, true);
    }

    /**
     * check value whether exists in array
     *
     * @static
     * @param array $arr
     * @param string $key
     * @param mixed $val
     * @return bool
     */
    public static function find($arr, $key, $val)
    {
        foreach ($arr as $val)
            if ($val == $val[$key])
                return true;

        return false;
    }

    /**
     * Arr::extract($_POST, array('username', 'password'))
     *
     * @static
     * @param array $arr
     * @param array $keys
     * @param mixed $default
     * @return array
     */
    public static function extract(array $arr, array $keys, $default = null)
    {
        $ret = array();
        foreach ($keys as $key)
            $ret[$key] = isset($arr[$key]) ? $arr[$key] : $default;

        return $ret;
    }

    /**
     * Arr::pluck($orm_result, 'username')
     *
     * @static
     * @param array $arr
     * @param $key
     * @return array
     */
    public static function pluck(array $arr, $key)
    {
        $ret = array();

        foreach ($arr as $row)
            if (isset($row[$key]))
                $ret[] = $row[$key];

        return $ret;
    }
}

/**
 * String
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

abstract class String
{
    /**
     * @param $val
     * @return string
     */
    public static function encode($val)
    {
        return \json_encode($val);
    }

    /**
     * @param $val
     * @return mixed
     */
    public static function decode($val)
    {
        if ($val)
            return \json_decode($val, true);

        return $val;
    }
}