<?php

/**
 * Cookie
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

namespace Parith\Lib;

class Cookie extends \Parith\Object
{
    protected $hash, $options = array('expire' => 7200, 'path' => '/', 'domain' => '', 'key' => null, 'hash' => false);

    /**
     * @param array $options
     * @return \Parith\Lib\Cookie
     */
    public function __construct(array $options = array())
    {
        $opt = $this->options = \Parith\App::option('Cookie', $options, $this->options);
        $opt['hash'] and $this->hash = $this->hashMethod($opt['key']);
    }

    /**
     * @param string $key
     * @return object
     */
    public function hashMethod($key)
    {
        return new \Parith\Lib\XXTEA($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->options['hash'] ? $this->hashGet($key) : $this->_get($key);
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire could be negative number
     * @return bool
     */
    public function set($key, $var, $expire = 0)
    {
        return $this->options['hash'] ? $this->hashSet($key, $var, $expire) : $this->_set($key, $var, $expire);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function hashGet($key)
    {
        $var = $this->hash->decrypt($this->_get($key));
        return $this->decode($var);
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire
     * @return bool
     */
    public function hashSet($key, $var, $expire = 0)
    {
        $str = $this->encode($var);
        return $this->_set($key, $this->hash->encrypt($str), $expire);
    }

    /**
     * @param mixed $var
     * @return string
     */
    public function encode($var)
    {
        return \json_encode($var);
    }

    /**
     * @param string $var
     * @return mixed
     */
    public function decode($var)
    {
        return $var ? \json_decode($var, true) : $var;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->set($key, '', -1);
    }

    /**
     * @return void
     */
    public function flush()
    {
        foreach ($_COOKIE as $key => $val)
            $this->delete($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function _get($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : false;
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire could be negative number
     * @return bool
     */
    public function _set($key, $var, $expire = 0)
    {
        $opt = $this->options;

        if ($expire > 0)
            $expire += APP_TIME;
        elseif ($expire == 0)
            $expire = $opt['expire'] + APP_TIME;

        $ret = setcookie($key, $var, $expire, $opt['path'], $opt['domain']);

        if ($ret === false)
            return $ret;

        $_COOKIE[$key] = $var;
        return true;
    }
}