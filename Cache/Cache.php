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
 * @link http://www.parith.net/
 */

namespace Parith\Cache;

class Cache
{
    public $options = array();

    private $_cache = array();

    public function __construct(array $options = array())
    {
        $this->options = \Parith\App::getOption('cache', $options) + $this->options;
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
     * @param $val
     * @return bool
     */
    public function set($key, $val)
    {
        $this->_cache[$key] = $val;
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