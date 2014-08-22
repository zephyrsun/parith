<?php

/**
 * Memcache
 *
 * for compatible with Memcached protocol (Memcached, Tokyo Tyrant, Tencent CMEM, etc.)
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

class Memcache extends Source
{
    public static $options = array(
        'host' => '127.0.0.1',
        'port' => 11211,
        'timeout' => 1,
        'compress' => 0,
        'persistent' => true,
        'weight' => 1,
        'retry_interval' => 15,
        'status' => true,
        'failure_callback' => null,
    );

    private $_compress;

    public function __construct(array $options = array())
    {
        $this->link = new \Memcache();
        parent::__construct($options);
    }

    /**
     * @param array $options
     * @return mixed|Memcache
     * @throws \Exception
     */
    public function connect(array $options)
    {
        $options = static::option($options);

        $this->connected = $this->link->connect($options['host'], $options['port'], $options['timeout']);

        if ($this->connected === false)
            throw new \Exception('Memcache could not connect to: ' . $options['host'] . ':' . $options['port']);

        $this->setCompress($options['compress']);

        return $this;
    }

    /**
     * @param array $options
     * @return Memcache
     * @throws \Exception
     */
    public function addServer(array $options)
    {
        $options = $this->option($options);

        $this->connected = $this->link->addServer($options['host'], $options['port'], $options['persistent'], $options['weight'],
            $options['timeout'], $options['retry_interval'], $options['status'], $options['failure_callback']);

        if ($this->connected === false)
            throw new \Exception('Memcache could not addServer: ' . $options['host'] . ':' . $options['port']);

        $this->setCompress($options['compress']);

        return $this;
    }

    /**
     * @param $compress
     * @return Memcache
     */
    public function setCompress($compress)
    {
        $this->_compress = $compress;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompress()
    {
        $ret = & $this->_compress;
        return $ret; // ? \MEMCACHE_COMPRESSED : 0;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->link->get($key, $this->getCompress());
    }

    /**
     * @param $key
     * @param $val
     * @param int $expire
     * @return bool
     */
    public function set($key, $val, $expire = 0)
    {
        return $this->link->set($key, $val, $this->getCompress(), $expire);
    }

    /**
     * @param $key
     * @param $val
     * @param int $expire
     * @return bool
     */
    public function add($key, $val, $expire = 0)
    {
        return $this->link->add($key, $val, $this->getCompress(), $expire);
    }

    /**
     * @param string $key
     * @param mixed $val
     * @param int $expire
     * @return bool
     */
    public function replace($key, $val, $expire = 0)
    {
        return $this->link->replace($key, $val, $this->getCompress(), $expire);
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function increment($key, $int = 1)
    {
        return $this->link->increment($key, $int);
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function decrement($key, $int = 1)
    {
        return $this->link->decrement($key, $int);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->link->delete($key);
    }

    /**
     * @return Memcache
     */
    public function flush()
    {
        $ret = $this->link->flush();

        // wait a second, this is necessary, or Memcached::set() will return 1, although your data is in fact not saved.
        sleep(1);

        return $ret;
    }

    /**
     * @return Memcache
     */
    public function close()
    {
        if ($this->connected)
            $this->link->close();

        return $this;
    }

    public function __destruct()
    {
        $this->close();
    }
}