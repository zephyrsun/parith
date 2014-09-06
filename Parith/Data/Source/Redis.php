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

use \Parith\Data\Source;

class Redis extends Source
{
    public $options = array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.0,
    );

    /**
     * @var \Redis
     */
    public $link;

    public function __construct(array $options = array())
    {
        $this->link = new \Redis();
        parent::__construct($options);
    }

    /**
     * @return \Redis
     * @throws \Exception
     */
    protected function connect()
    {
        $options = & $this->options;

        $this->connected = $this->link->connect($options['host'], $options['port'], $options['timeout']);

        if (!$this->connected)
            throw new \Exception('Fail to connect Redis server: ' . $this->instanceKey());

        return $this->link;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return \call_user_func_array(array($this->link, $method), $args);
    }

    /**
     * @return Redis
     */
    public function close()
    {
        if ($this->connected)
            $this->link->close();

        return $this;
    }
}