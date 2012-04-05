<?php

/**
 * Redis
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

namespace Parith\DataSource;

class DataSource
{
    public $configs = array(), $defaults = array();

    public function config($name)
    {
        $this->configs = \Parith\App::config($name);
        return $this;
    }

    public function option($id, array $options = array())
    {
        if (is_array($id)) { //$id equals $options
            return $id + $this->defaults;
        }

        $configs = &$this->configs[$id];
        if (is_array($configs)) {
            return $options + $configs + $this->defaults;
        }

        throw new \Parith\Exception('Unknown ' . get_called_class() . ' config ID: ' . $id);
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