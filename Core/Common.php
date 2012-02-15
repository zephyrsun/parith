<?php

/**
 * Object
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

abstract class Object
{
    /**
     * @param $name
     * @return object
     */
    public static function getInstance($name = null)
    {
        static $cache = array();

        $name or $name = get_called_class();

        $obj = &$cache[$name];
        if ($obj)
            return $obj;

        $args = func_get_args();
        switch (count($args))
        {
            case 1:
                return $obj = new $name($args[0]);
            case 2:
                return $obj = new $name($args[0], $args[1]);
            case 3:
                return $obj = new $name($args[0], $args[1], $args[2]);
            case 4:
                return $obj = new $name($args[0], $args[1], $args[2], $args[3]);
            default:
                return $obj = new $name;
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
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
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
        return isset($arr[$key]) ? $arr[$key] : $default;
    }

    /**
     * @static
     * @param array $arr
     * @param string $key
     * @param mixed $var
     * @param string $delimiter
     * @return array
     */
    public static function pathSet(array &$arr, $key, $var, $delimiter = ',')
    {
        foreach (\explode($delimiter, $key) as $val)
            $arr = &$arr[$val] or $arr = array();

        return $arr = $var;
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
            if (!$arr = &$arr[$val])
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
        $var = \explode('.', $key);
        $last = \end($var);

        foreach ($var as $val)
        {
            if ($val === $last)
                unset($arr[$val]);
            else
                $arr = &$arr[$val];
        }
    }

    /**
     * Arr::unshift($array, 'first element', 'element value')
     *
     * @static
     * @param array $arr
     * @param string $key
     * @param mixed $var
     * @return array
     */
    public static function unshift(array &$arr, $key, $var)
    {
        $arr = \array_reverse($arr, true);
        $arr[$key] = $var;
        return \array_reverse($arr, true);
    }

    /**
     * check value whether exists in array
     *
     * @static
     * @param array $arr
     * @param string $key
     * @param mixed $var
     * @return bool
     */
    public static function find($arr, $key, $var)
    {
        foreach ($arr as $val)
            if ($var == $val[$key])
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

    /**
     * get multi random number
     *
     * @static
     * @param $min
     * @param $max
     * @param $num
     * @return array
     */
    public static function multiRand($min, $max, $num)
    {
        $t = array();
        do {
            $t[mt_rand($min, $max)] = 1;
        } while (\count($t) < $num);

        return \array_keys($t);
    }
}