<?php

/**
 * Redis
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Data\Source;

class Redis extends \Parith\Data\Source
{
    public static $options = array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.0,
    );

    public function __construct(array $options = array())
    {
        $this->ds = new \Redis();
        parent::__construct($options);
    }

    /**
     * @param array $options
     * @return mixed|Redis
     * @throws \Parith\Exception
     */
    public function connect(array $options)
    {
        $options = static::option($options);

        $this->link = $this->ds->connect($options['host'], $options['port'], $options['timeout']);
        if ($this->link === false)
            throw new \Parith\Exception('Redis could not connect to ' . $options['host'] . ':' . $options['port']);

        return $this;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return \call_user_func_array(array($this->ds, $method), $args);
    }

    /**
     * @return Redis
     */
    public function close()
    {
        if ($this->link)
            $this->ds->close();

        return $this;
    }
}