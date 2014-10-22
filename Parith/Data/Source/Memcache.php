<?php

/**
 * Memcache
 * for compatible with Memcached protocol (Memcached, Tokyo Tyrant, Tencent CMEM, etc.)
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

class Memcache extends Source
{
    public $options = array(
        'host' => '127.0.0.1',
        'port' => 11211,
        'timeout' => 1,
        'persistent' => true,
        'weight' => 1,
        'retry_interval' => 15,
        'status' => true,
        'failure_callback' => NULL,
    );

    /**
     * @var \Memcache
     */
    public $link;

    public $connected = false;

    public $compress = 0; //\MEMCACHE_COMPRESSED

    /**
     * @return \Memcache
     */
    protected function getLink()
    {
        $this->link = new \Memcache();

        $options = & $this->options;

        $this->connected = $this->link->connect($options['host'], $options['port'], $options['timeout']);

        //if (!$this->connected)
        //    throw new \Exception('Fail to connect Memcache server: ' . $this->instanceKey());

        return $this->link;
    }

    /**
     * addServer(array(1 => $options, 2 => $options))
     *
     * @param array $server_array
     *
     * @return bool|\Memcache
     */
    public function addServer(array $server_array)
    {
        $this->link = new \Memcache();

        foreach ($server_array as $options) {
            $options = $this->option($options);

            $this->connected = $this->link->addServer($options['host'], $options['port'], $options['persistent'], $options['weight'],
                $options['timeout'], $options['retry_interval'], $options['status'], $options['failure_callback']);

            //if (!$this->connected)
            //    throw new \Exception('Fail to add Memcache server: ' . $this->instanceKey());
        }

        return $this->link;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->link->get($key, $this->compress);
    }

    /**
     * @param     $key
     * @param     $val
     * @param int $expire
     *
     * @return bool
     */
    public function set($key, $val, $expire = 0)
    {
        return $this->link->set($key, $val, $this->compress, $expire);
    }

    /**
     * @param     $key
     * @param     $val
     * @param int $expire
     *
     * @return bool
     */
    public function add($key, $val, $expire = 0)
    {
        return $this->link->add($key, $val, $this->compress, $expire);
    }

    /**
     * @param string $key
     * @param mixed  $val
     * @param int    $expire
     *
     * @return bool
     */
    public function replace($key, $val, $expire = 0)
    {
        return $this->link->replace($key, $val, $this->compress, $expire);
    }

    /**
     * @param string $key
     * @param int    $int
     *
     * @return int
     */
    public function increment($key, $int = 1)
    {
        return $this->link->increment($key, $int);
    }

    /**
     * @param string $key
     * @param int    $int
     *
     * @return int
     */
    public function decrement($key, $int = 1)
    {
        return $this->link->decrement($key, $int);
    }

    /**
     * @param $key
     *
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
        if ($this->connected) {
            $this->link->close();
        }

        return $this;
    }

    public function __destruct()
    {
        $this->close();
    }
}