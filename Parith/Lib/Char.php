<?php

/**
 * Char
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

class Char
{
    /**
     * Based on BKDR (Brian Kernighan and Dennis Ritchie) hash function,
     * not DJBX33A (Daniel J. Bernstein, Times 33 with Addition) hash function
     *
     * @static
     *
     * @param string $str
     * @param int $s In my test, number 5381 distributes better than number 131
     * @param int $hash
     *
     * @return int
     */
    static public function hash($str, $s = 5381, $hash = 0)
    {
        foreach (str_split($str) as $v)
            $hash = ($hash * $s + ord($v)) & 0x7FFFFFFF;

        return $hash;
    }

    static public function nonce($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $c_len = strlen($chars);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, $c_len - 1), 1);
        }
        return $str;
    }

    static public function split($str, $l, $enc = 'UTF-8')
    {
        $arr = [];
        $len = mb_strlen($str, $enc);
        for ($i = 0; $i < $len; $i += $l)
            $arr[] = mb_substr($str, $i, $l, $enc);
        return $arr;
    }

    /**
     * @static
     *
     * @param $str
     *
     * @return string
     */
    static public function sanitize($str)
    {
        return \htmlspecialchars($str, ENT_QUOTES);
    }

    /**
     * @static
     *
     * @param $str
     *
     * @return bool
     */
    static public function isAscii($str)
    {
        return !preg_match('/[^\x00-\x7F]/', $str);
    }

    /**
     * @param $str
     * @param string $delimiter
     * @param callable $filter
     * @return array
     */
    static public function toArray($str, $delimiter = ',', callable $filter = null)
    {
        $arr = \explode($delimiter, $str);

        if ($filter)
            $arr = \array_map($filter, $arr);

        return $arr;
    }
}