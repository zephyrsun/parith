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

abstract class Object
{
    private static $_instances = array();

    /**
     * \Parith\Lib\File::factory();
     *
     * @static
     * @return object
     */
    public static function factory()
    {
        return static::getInstance(get_called_class(), func_get_args());
    }

    /**
     * @static
     * @param $class
     * @param $args
     * @param null $key
     * @return mixed
     */
    public static function getInstance($class, $args = array(), $key = null)
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
            case 4:
                return $obj = new $class($args[0], $args[1], $args[2], $args[3]);
            default:
                return $obj = new $class();
        }
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