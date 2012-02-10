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

class Redis extends DataSource
{
    public $prefix = '', $key, $link, $redis, $default = array(
        'host' => null, 'port' => 6379, 'timeout' => 0.0
    );

    /**
     * @return Redis
     */
    public function __construct()
    {
        $this->option('Redis');

        $this->redis = $this->getBaseClass();
    }

    public function getBaseClass()
    {
        return new \Redis();
    }

    /**
     * @param $id
     * @param array $options
     * @return Redis
     */
    public function connectById($id, array $options = array())
    {
        $options += $this->initServer($id, $options);
        return $this->connect($options);
    }

    /**
     * @param $options
     * @return Redis
     * @throws \Parith\Exception
     */
    public function connect($options)
    {
        $this->link = $this->redis->connect($options['host'], $options['port'], $options['timeout']);
        if ($this->link === false)
            throw new \Parith\Exception('Redis could not connect to ' . $options['host'] . ':' . $options['port']);

        return $this;
    }

    /**
     * @param $key
     * @return string
     */
    public function getKey($key)
    {
        return $this->key = $this->prefix . $key;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $ret = \call_user_func_array(array($this->redis, $method), $arguments);

        return $ret;
    }

    /**
     * @return Redis
     */
    public function close()
    {
        $this->link and $this->redis->close();
        return $this;
    }
}