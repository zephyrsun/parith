<?php

/**
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

namespace Parith\DataSource;

class Memcache extends DataSource
{
    public $link, $memcache, $compress = 0, $defaults = array(
        'host' => '127.0.0.1', 'port' => 11211, 'timeout' => 1, 'compress' => 0,
        'persistent' => true, 'weight' => 1,
        'retry_interval' => 15, 'status' => true, 'failure_callback' => null,
    );

    /**
     * @param string $option_name
     * @return Memcache
     */
    public function __construct($option_name = 'Memcache')
    {
        $this->option($option_name);

        $this->memcache = new \Memcache();
    }

    /**
     * @param $id
     * @param bool $add_server
     * @return Memcache
     */
    public function connectById($id, $add_server = false)
    {
        $options = $this->drawOption($id);

        return $add_server ? $this->addServer($options) : $this->connect($options);
    }

    /**
     * @param $options
     * @return Memcache
     * @throws \Parith\Exception
     */
    public function connect($options)
    {
        $options = $this->normalizeOption($options);

        $this->setCompress($options['compress']);

        $link = $this->memcache->connect($options['host'], $options['port'], $options['timeout']);
        if ($link === false)
            throw new \Parith\Exception('Memcache could not connect to ' . $options['host'] . ':' . $options['port']);

        $this->link = $link;
        return $this;
    }

    /**
     * @param $options
     * @return Memcache
     * @throws \Parith\Exception
     */
    public function addServer($options)
    {
        $options = $this->normalizeOption($options);

        $this->setCompress($options['compress']);

        $link = $this->memcache->addServer($options['host'], $options['port'], $options['persistent'], $options['weight'],
            $options['timeout'], $options['retry_interval'], $options['status'], $options['failure_callback']);

        if ($link === false)
            throw new \Parith\Exception('Memcache could not connect to ' . $options['host'] . ':' . $options['port']);

        $this->link = $link;
        return $this;
    }

    /**
     * @return Memcache
     */
    public function connectAll()
    {
        foreach ($this->options as &$cfg)
            $this->addServer($cfg);

        return $this;
    }

    /**
     * @return Memcache
     */
    public function close()
    {
        if ($this->link)
            $this->memcache->close();

        return $this;
    }

    /**
     * @param $compress
     * @return Memcache
     */
    public function setCompress($compress)
    {
        $this->compress = $compress;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompress()
    {
        $ret = &$this->compress;
        return $ret; // ? \MEMCACHE_COMPRESSED : 0;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcache->get($key, $this->getCompress());
    }

    /**
     * @param $key
     * @param $var
     * @param int $expire
     * @return bool
     */
    public function set($key, $var, $expire = 0)
    {
        return $this->memcache->set($key, $var, $this->getCompress(), $expire);
    }

    /**
     * @param $key
     * @param $var
     * @param int $expire
     * @return bool
     */
    public function add($key, $var, $expire = 0)
    {
        return $this->memcache->add($key, $var, $this->getCompress(), $expire);
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire
     * @return bool
     */
    public function replace($key, $var, $expire = 0)
    {
        return $this->memcache->replace($key, $var, $this->getCompress(), $expire);
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function increment($key, $int = 1)
    {
        return $this->memcache->increment($key, $int);
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function decrement($key, $int = 1)
    {
        return $this->memcache->decrement($key, $int);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->memcache->delete($key);
    }

    /**
     * @return Memcache
     */
    public function flush()
    {
        $ret = $this->memcache->flush();

        // wait a second, this is necessary, or Memcached::set() will return 1, although your data is in fact not saved.
        sleep(1);

        return $ret;
    }
}