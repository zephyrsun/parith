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

class Cookie extends \Parith\Object
{
    public $options = array(
        'expire' => 7200,
        'path' => '/',
        'domain' => '',
        'handler' => null, //must be an Object
    )
    , $handler;

    /**
     * @param array $options
     * @return \Parith\Lib\Cookie
     */
    public function __construct(array $options = array())
    {
        $this->options = \Parith\App::getOption('cookie', $options) + $this->options;
        if ($this->options['hash_object'])
            $this->handler = $this->options['handler']; //new \Parith\Lib\XXTEA($key)
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_COOKIE[$key])) {
            $ret = $_COOKIE[$key];

            if ($this->handler)
                return \Parith\String::decode($this->handler->decrypt($ret));

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
        if ($this->handler)
            $val = $this->handler->encrypt(\Parith\String::encode($val));

        if ($expire > 0)
            $expire += APP_TS;
        elseif ($expire == 0)
            $expire = $this->options['expire'] + APP_TS;

        $ret = setcookie($key, $val, $expire, $this->options['path'], $this->options['domain']);

        if ($ret === false)
            return $ret;

        $_COOKIE[$key] = $val;
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