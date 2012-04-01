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
    public $options = array(), $defaults = array();

    public function option($name)
    {
        $this->options = \Parith\App::option($name, array(), $this->defaults);

        return $this;
    }

    /**
     * @param int $id
     * @return array
     */
    public function drawOption($id)
    {
        $cfg = &$this->options[$id];

        if (is_array($cfg))
            return $cfg;

        throw new \Parith\Exception('Unknown ' . get_called_class() . ' config ID: ' . $id);
    }

    public function normalizeOption(array $options)
    {
        return $options + $this->defaults;
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