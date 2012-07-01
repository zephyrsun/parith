<?php

/**
 * Cache
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

namespace Parith\Cache;

class Cache
{
    public $options = array();

    private $_cache = array();

    /**
     * @param $name
     * @param array $options
     * @return array
     */
    public function config($name, array $options = array())
    {
        return $this->options = \Parith\App::getOption($name, $options) + $this->options;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return \Parith\Arr::get($this->_cache, $key, null);
    }

    /**
     * @param $key
     * @param $var
     * @return bool
     */
    public function set($key, $var)
    {
        $this->_cache[$key] = $var;
        return true;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        unset($this->_cache[$key]);
        return true;
    }

    /**
     * @return bool
     */
    public function flush()
    {
        $this->_cache = array();
        return true;
    }
}