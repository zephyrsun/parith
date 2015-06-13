<?php

/**
 * Data Source
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Data;

abstract class Source
{
    private static $_ins = array();

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param string $key key for different instance
     * @param array $options
     * @return object
     */
    public static function getInstance($key, array $options = array())
    {
        $class = \get_called_class();

        $obj = & self::$_ins[$class . $key];
        if ($obj)
            return $obj;

        return $obj = new $class($options);
    }

    /**
     * @param $key
     * @return array
     */
    public static function getOption($key)
    {
        return array();
    }

    /**
     * @return null
     */
    abstract public function close();
}