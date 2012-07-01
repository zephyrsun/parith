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
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith\Data\Source;

class Memcache extends \Parith\Data\Source
{
    public static $options = array(
        'host' => '127.0.0.1', 'port' => 11211, 'timeout' => 1, 'compress' => 0,
        'persistent' => true, 'weight' => 1,
        'retry_interval' => 15, 'status' => true, 'failure_callback' => null,
    );

    private $_compress;

    public function __construct()
    {
        $this->ds = $this->getBaseClass();
        parent::__construct();
    }

    public function getBaseClass()
    {
        return new \Memcache();
    }

    /**
     * @param array $options
     * @return mixed|Memcache
     * @throws \Parith\Exception
     */
    public function connect(array $options)
    {
        $options = static::option($options);

        $this->link = $this->ds->connect($options['host'], $options['port'], $options['timeout']);

        if ($this->link === false)
            throw new \Parith\Exception('Memcache could not connect to: ' . $options['host'] . ':' . $options['port']);

        $this->setCompress($options['compress']);

        return $this;
    }

    /**
     * @param array $options
     * @return Memcache
     * @throws \Parith\Exception
     */
    public function addServer(array $options)
    {
        $options = $this->option($options);

        $this->link = $this->ds->addServer($options['host'], $options['port'], $options['persistent'], $options['weight'],
            $options['timeout'], $options['retry_interval'], $options['status'], $options['failure_callback']);

        if ($this->link === false)
            throw new \Parith\Exception('Memcache could not addServer: ' . $options['host'] . ':' . $options['port']);

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
        $ret = &$this->_compress;
        return $ret; // ? \MEMCACHE_COMPRESSED : 0;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->ds->get($key, $this->getCompress());
    }

    /**
     * @param $key
     * @param $var
     * @param int $expire
     * @return bool
     */
    public function set($key, $var, $expire = 0)
    {
        return $this->ds->set($key, $var, $this->getCompress(), $expire);
    }

    /**
     * @param $key
     * @param $var
     * @param int $expire
     * @return bool
     */
    public function add($key, $var, $expire = 0)
    {
        return $this->ds->add($key, $var, $this->getCompress(), $expire);
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire
     * @return bool
     */
    public function replace($key, $var, $expire = 0)
    {
        return $this->ds->replace($key, $var, $this->getCompress(), $expire);
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function increment($key, $int = 1)
    {
        return $this->ds->increment($key, $int);
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function decrement($key, $int = 1)
    {
        return $this->ds->decrement($key, $int);
    }

    /**
     * @param $key
     * @param array $options
     * @return bool
     */
    public function delete($key, array $options = array())
    {
        return $this->ds->delete($key);
    }

    /**
     * @return Memcache
     */
    public function flush()
    {
        $ret = $this->ds->flush();

        // wait a second, this is necessary, or Memcached::set() will return 1, although your data is in fact not saved.
        sleep(1);

        return $ret;
    }

    /**
     * @return Memcache
     */
    public function close()
    {
        if ($this->link)
            $this->ds->close();

        return $this;
    }
}