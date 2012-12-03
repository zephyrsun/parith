<?php

/**
 * Cookie
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

namespace Parith\Lib;

class Cookie extends \Parith\Object
{
    public $options = array(
        'expire' => 7200,
        'path' => '/',
        'domain' => '',
        'key' => null,
        'hash' => false,
    )
    , $hash;

    /**
     * @param array $options
     * @return \Parith\Lib\Cookie
     */
    public function __construct(array $options = array())
    {
        $this->options = \Parith\App::getOption('cookie', $options) + $this->options;
        if ($this->options['hash'])
            $this->hash = $this->hashMethod($this->options['key']);
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
        if ($this->options['hash'])
            return $this->hashGet($key);

        return $this->_get($key);
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire could be negative number
     * @return bool
     */
    public function set($key, $var, $expire = 0)
    {
        if ($this->options['hash'])
            return $this->hashSet($key, $var, $expire);

        return $this->_set($key, $var, $expire);
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
        if ($var)
            return \json_decode($var, true);

        return $var;
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
        if (isset($_COOKIE[$key]))
            return $_COOKIE[$key];

        return null;
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
            $expire += APP_TS;
        elseif ($expire == 0)
            $expire = $opt['expire'] + APP_TS;

        $ret = setcookie($key, $var, $expire, $opt['path'], $opt['domain']);

        if ($ret === false)
            return $ret;

        $_COOKIE[$key] = $var;
        return true;
    }
}