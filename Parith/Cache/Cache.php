<?php

/**
 * Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Cache;

use \Parith\App;
use \Parith\Result;

class Cache extends Result
{
    public $options = [];

    public function __construct()
    {
        $this->options = App::getOption('cache') + $this->options;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->resultGet($key);
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function set($key, $val)
    {
        return $this->resultSet($key, $val);
    }

    /**
     * @param $key
     * @return $this
     */
    public function delete($key)
    {
        return $this->resultDelete($key);
    }

    /**
     * @return Result
     */
    public function flush()
    {
        return $this->resultFlush();
    }
}