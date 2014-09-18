<?php

/**
 * Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Cache;

use \Parith\App;
use \Parith\Result;

class Cache extends Result
{
    public $options = array();

    public function __construct(array $options = array())
    {
        $this->options = $options + App::getOption('cache') + $this->options;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return parent::resultGet($key);
    }

    /**
     * @param $key
     * @param $val
     *
     * @return bool
     */
    public function set($key, $val)
    {
        return parent::resultSet($key, $val);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function delete($key)
    {
        return parent::resultDelete($key);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return parent::resultFlush();
    }
}