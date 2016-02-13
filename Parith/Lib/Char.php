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
     * @static
     *
     * @param          $str
     * @param string $delimiter
     * @param function $filter
     *
     * @return array
     */
    static public function toArray($str, $delimiter = ',', $filter = null)
    {
        $arr = \explode($delimiter, $str);

        if ($filter)
            $arr = \array_map($filter, $arr);

        return $arr;
    }
}