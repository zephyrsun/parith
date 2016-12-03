<?php

/**
 * Data Source
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\DataSource;

abstract class Basic
{
    static protected $ins_n = 0;
    static protected $ins_link = [];

    public function __destruct()
    {
        if (--static::$ins_n == 0) {
            $this->closeAll();
            static::$ins_link = [];
        }
    }

    /**
     * @return mixed
     */
    static public function getInstance()
    {
        static $ins = [];

        $obj = &$ins[static::class] or $obj = new static();

        return $obj;
    }

    /**
     * @param $options
     * @return $this
     */
    abstract public function dial($options);

    abstract public function closeAll();
}