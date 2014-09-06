<?php

/**
 * Cookie
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Lib;

use \Parith\Result;
use \Parith\App;
use \Parith\String;

class Cookie extends Result
{
    public $options = array(
        'expire' => 7200,
        'path' => '/',
        'domain' => '',
        'crypt' => '\Parith\Lib\CookieHandler',
        'crypt_key' => 'crypt.cookie.parith'
    )
    , $crypt;

    /**
     * @param array $options
     * @return \Parith\Lib\Cookie
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + App::getOption('cookie') + $this->options;

        if ($this->options['crypt'])
            $this->crypt = new $this->options['crypt']($this->options['crypt_key']);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_COOKIE[$key])) {
            $ret = $_COOKIE[$key];

            if ($this->crypt)
                return $this->crypt->decrypt($ret);

            return $ret;
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $val
     * @param int $expire could be negative number
     * @return bool
     */
    public function set($key, $val, $expire = 0)
    {
        if ($this->crypt)
            $val = $this->crypt->encrypt($val);

        if ($expire > 0)
            $expire += APP_TS;
        elseif ($expire == 0)
            $expire = $this->options['expire'] + APP_TS;

        $ret = setcookie($key, $val, $expire, $this->options['path'], $this->options['domain']);

        if ($ret === false)
            return $ret;

        //$_COOKIE[$key] = $val;
        return true;
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
}

class CookieHandler
{
    protected $key = 0, $key_length = 0;

    public function __construct($key = '')
    {
        if ($key)
            $this->setKey($key);
    }

    public function setKey($key)
    {
        $new_key = 0;
        $length = strlen($key);
        for ($i = 0; $i < $length; $i++) {
            $new_key += \ord($key[$i]);
        }

        $this->key = $new_key;
        $this->key_length = strlen(\ord('/') ^ $this->key);
    }

    public function encrypt($val)
    {
        $val = String::encode($val);

        $ret = '';
        $length = strlen($val);
        for ($i = 0; $i < $length; $i++) {
            $ret .= sprintf('%0' . $this->key_length . 'd', \ord($val[$i]) ^ $this->key);
        };

        return $ret;
    }

    public function decrypt($val)
    {
        $ret = '';
        foreach (\str_split($val, $this->key_length) as $v) {
            $ret .= chr($v ^ $this->key);
        }

        return String::decode($ret);
    }
}