<?php

/**
 * Redis
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
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

    protected function __construct($options)
    {
        $this->options = $options + $this->options;
        $this->connect();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function connect()
    {
        $this->link = new \Redis();

        $options = $this->options;

        $connected = $this->link->connect($options['host'], $options['port'], $options['timeout']);
        if (!$connected)
            throw new \Exception("Fail to connect: {$options['host']}:{$options['port']}");

        //$this->link->setOption(\Redis::OPT_READ_TIMEOUT, -1);

        return $this;
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function call($method, $args)
    {
        return \call_user_func_array(array($this->link, $method), $args);
    }

    /**
     * @return Redis
     */
    public function close()
    {
        $this->link->close();

        return $this;
    }
}