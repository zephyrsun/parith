<?php
/**
 * Database with Memcached
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\DataSource;

class MemcachedDB extends PDO
{
    /**
     * @var Memcached
     */
    public $mc;

    /**
     * @var string primary key
     */
    public $pk = 'id';

    public function __construct()
    {
        $this->mc = new Memcached();
        parent::__construct();
    }

    /**
     * use for hash, redial memcache here
     *
     * @param $key
     * @return \Memcached|string
     */
    public function hash($key)
    {
        return $key;
    }

    public function get($key)
    {
        $k = $this->hash($key);

        $mc = $this->mc->dial($key);

        $ret = $mc->get($k);
        if (!$ret) {
            $this->dial($key);
            $ret = $this->where($this->pk, $key)->fetch();
            if ($ret)
                $mc->set($k, $ret);
        }

        return $ret;
    }

    public function set($key, $data, $expiration = 0)
    {
        $k = $this->hash($key);

        $this->dial($key);

        $ret = $this->where($this->pk, $key)->update($data);
        if ($ret) {
            $data = $this->where($this->pk, $key)->fetch();
            if ($data) {
                $mc = $this->mc->dial($key);
                $mc->set($k, $data, $expiration);
            }
        }

        return $ret;
    }

    public function delete($key)
    {
        $mc = $this->mc->dial($key);
        return $mc->delete($this->hash($key));
    }
} 