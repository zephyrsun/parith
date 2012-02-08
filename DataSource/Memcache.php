<?php

/**
 * for compatible with Memcached protocol (Memcached, Tokyo Tyrant, Tencent CMEM, etc.)
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2011 Zephyr Sun
 * @license http://www.parith.org/license
 * @version 0.3
 * @link http://www.parith.org/
 */

namespace Parith\DataSource;

class Memcache extends DataSource
{
    public $prefix = '', $key, $link, $memcache, $cache, $expire = 2592000, $compress = 0, $default = array(
        'host' => '127.0.0.1', 'port' => 11211, 'timeout' => 1, 'compress' => 0,
        'persistent' => true, 'weight' => 1,
        'retry_interval' => 15, 'status' => true, 'failure_callback' => null,
    );

    /**
     * @param string $cfg_name
     * @return Memcache
     */
    public function __construct($cfg_name = 'Memcache')
    {
        parent::option($cfg_name);
        $this->memcache = new \Memcache();
        $this->cache = new \Parith\Cache\Cache();
    }

    /**
     * @param $id
     * @param bool $add_server
     * @return Memcache
     */
    public function connectById($id, $add_server = false)
    {
        $options = $this->initServer($id);

        $this->setCompress($options['compress']);

        return $add_server ? $this->addServer($options) : $this->connect($options);
    }

    /**
     * @param $options
     * @return Memcache
     * @throws \Parith\Exception
     */
    public function connect($options)
    {
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
        foreach ($this->servers as &$cfg)
            $this->addServer($cfg);

        return $this;
    }

    /**
     * @return Memcache
     */
    public function close()
    {
        $this->link and $this->memcache->close();
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
     * @param $expire
     * @return Memcache
     */
    public function setExpire($expire)
    {
        $this->expire = (int)$expire;
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
        $pk = $this->getKey($key);

        $ret = $this->cache->get($pk);

        if ($ret === null) {
            $ret = $this->memcache->get($pk, $this->getCompress());
            $ret === false or $this->cache->set($pk, $ret);
        }

        return $ret;
    }

    /**
     * @param $key
     * @param $var
     * @param int $expire
     * @return bool
     */
    public function set($key, $var, $expire = 0)
    {
        $expire or $expire = $this->expire;

        $pk = $this->getKey($key);

        $ret = $this->memcache->set($pk, $var, $this->getCompress(), $expire);
        $ret === false or $this->cache->set($pk, $var);

        return $ret;
    }

    /**
     * @param $key
     * @param $var
     * @param int $expire
     * @return bool
     */
    public function add($key, $var, $expire = 0)
    {
        $expire or $expire = $this->expire;

        $pk = $this->getKey($key);

        $ret = $this->memcache->add($pk, $var, $this->getCompress(), $expire);
        $ret === false or $this->cache->set($pk, $var);

        return $ret;
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire
     * @return bool
     */
    public function replace($key, $var, $expire = 0)
    {
        $pk = $this->getKey($key);

        $ret = $this->memcache->replace($pk, $var, $this->getCompress(), $expire);
        $ret === false or $this->cache->set($pk, $var);

        return $ret;
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function increment($key, $int = 1)
    {
        $pk = $this->getKey($key);

        $ret = $this->memcache->increment($pk, $int);
        $ret === false or $this->cache->set($pk, $ret);

        return $ret;
    }

    /**
     * @param string $key
     * @param int $int
     * @return int
     */
    public function decrement($key, $int = 1)
    {
        $pk = $this->getKey($key);

        $ret = $this->memcache->decrement($pk, $int);
        $ret === false or $this->cache->set($pk, $ret);

        return $ret;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $pk = $this->getKey($key);

        $ret = $this->memcache->delete($pk);
        $ret and $this->cache->delete($pk);

        return $ret;
    }

    /**
     * @return Memcache
     */
    public function flush()
    {
        $ret = $this->memcache->flush();

        # wait a second, this is necessary, or Memcached::set() will return 1, although your data is in fact not saved.
        sleep(1);

        $ret and $this->cache->flush();

        return $ret;
    }

    /**
     * @param $key
     * @return string
     */
    public function getKey($key)
    {
        return $this->key = $this->prefix . $key;
    }
}