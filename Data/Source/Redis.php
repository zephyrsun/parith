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

namespace Parith\Data\Source;

class Redis extends \Parith\Data\Source
{
    public $options = array('host' => '127.0.0.1', 'port' => 6379, 'timeout' => 0.0);

    public function __construct()
    {
        $this->ds = $this->getBaseClass();
        parent::__construct();
    }

    public function getBaseClass()
    {
        return new \Redis();
    }

    /**
     * @param array $options
     * @return Redis
     * @throws \Parith\Exception
     */
    public function connect($options = array())
    {
        $options = $this->option($options);

        $this->link = $this->ds->connect($options['host'], $options['port'], $options['timeout']);
        if ($this->link === false)
            throw new \Parith\Exception('Redis could not connect to ' . $options['host'] . ':' . $options['port']);

        return $this;
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