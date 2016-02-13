<?php

/**
 * Data Source
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\DataSource;

abstract class Basic
{
    static protected $ins_n = 0;
    static protected $ins_link = array();

    public function __destruct()
    {
        if (--static::$ins_n == 0)
            $this->closeAll();
    }

    abstract public function closeAll();

    /**
     * @return mixed
     */
    static public function getInstance()
    {
        static $ins = array();

        $class = \get_called_class();

        $obj = $ins[$class] ?? new $class();

        return $obj;
    }

    /**
     * @param $options
     * @return $this
     */
    abstract public function dial($options);
}