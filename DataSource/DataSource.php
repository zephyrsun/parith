<?php

/**
 * Redis
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2011 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith\DataSource;

class DataSource
{
    public $servers = array(), $default = array();

    public function option($name)
    {
        $this->servers = \Parith\App::option($name, array(), $this->default);

        return $this;
    }

    /**
     * @param int $id
     * @return array
     */
    public function initServer($id)
    {
        $cfg = &$this->servers[$id];

        if (!is_array($cfg))
            throw new \Parith\Exception('Unknown ' . get_called_class() . ' config ID: ' . $id);

        return $cfg + $this->default;
    }

    /**
     * @param mixed $var
     * @return string
     */
    public function encode($var)
    {
        return \json_encode($var);
    }

    /**
     * @param string $var
     * @return mixed
     */
    public function decode($var)
    {
        return $var ? \json_decode($var, true) : $var;
    }
}