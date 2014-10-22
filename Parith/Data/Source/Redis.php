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

    public $connected = false;

    /**
     * @return \Redis
     * @throws \Exception
     */
    protected function getLink()
    {
        $this->link = new \Redis();

        $options = & $this->options;

        $this->connected = $this->link->connect($options['host'], $options['port'], $options['timeout']);

        if (!$this->connected)
            throw new \Exception('Fail to connect Redis server: ' . $this->instanceKey());

        return $this->link;
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