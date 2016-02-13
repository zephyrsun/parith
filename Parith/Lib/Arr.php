<?php

/**
 * Arr
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */
abstract class Arr
{
    /**
     * @static
     *
     * @param array $arr
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    static public function get(array $arr, $key, $default = null)
    {
        if (isset($arr[$key]))
            return $arr[$key];

        return $default;
    }

    /**
     * @static
     *
     * @param array $arr
     * @param string $key
     * @param mixed $val
     * @param string $delimiter
     *
     * @return array
     */
    static public function pathSet(array &$arr, $key, $val, $delimiter = ',')
    {
        foreach (\explode($delimiter, $key) as $v)
            $arr = $arr[$v] ?? array();

        return $arr = $val;
    }

    /**
     * @static
     *
     * @param array $arr
     * @param string $key
     * @param string $delimiter
     *
     * @return mixed
     */
    static public function pathGet(array &$arr, $key, $delimiter = ',')
    {
        foreach (\explode($delimiter, $key) as $val)
            if (!$arr = & $arr[$val])
                return false;

        return $arr;
    }

    /**
     * @static
     *
     * @param array $arr
     * @param string $key
     *
     * @return void
     */
    static public function pathDelete(array &$arr, $key)
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
     *
     * @param array $arr
     * @param string $key
     * @param mixed $val
     *
     * @return array
     */
    static public function unshift(array &$arr, $key, $val)
    {
        $arr = \array_reverse($arr, true);
        $arr[$key] = $val;
        return \array_reverse($arr, true);
    }

    /**
     * check value whether exists in array
     *
     * @static
     *
     * @param array $arr
     * @param string $key
     * @param mixed $val
     *
     * @return bool
     */
    static public function find($arr, $key, $val)
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
     *
     * @param array $arr
     * @param array $keys
     * @param mixed $default
     *
     * @return array
     */
    static public function extract(array $arr, array $keys, $default = null)
    {
        $ret = array();
        foreach ($keys as $key)
            $ret[$key] = isset($arr[$key]) ?$arr[$key]: $default;

        return $ret;
    }

    /**
     * Arr::pluck($orm_result, 'username')
     *
     * @static
     *
     * @param array $arr
     * @param       $key
     *
     * @return array
     */
    static public function pluck(array $arr, $key)
    {
        $ret = array();

        foreach ($arr as $row)
            if (isset($row[$key]))
                $ret[] = $row[$key];

        return $ret;
    }
}