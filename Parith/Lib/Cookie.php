<?php

/**
 * Cookie
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

use Parith\Result;

class Cookie extends Result
{
    public $options = [
        'expire' => 86400,//seconds
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'token_key' => 'token',
        'encryptor' => '\Parith\Lib\Crypt',//'\Parith\Lib\JWTAuth'
    ];

    public $enc;

    /**
     * Cookie constructor.
     */
    public function __construct()
    {
        $this->setOptions(\Parith::getEnv('cookie'));

        $this->__ = &$_COOKIE;

        $this->enc = new $this->options['encryptor'];
    }

    public function p3p()
    {
        \header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

        return $this;
    }

    /**
     * with encryptor
     *
     * @param $data
     * @param string $key
     * @return mixed
     */
    public function setToken($data, $key = ' ')
    {
        $key = \APP_TS . $key;
        return $this->set($this->options['token_key'], $this->enc->keyEncrypt($data, $key));
    }

    /**
     * with encryptor
     *
     * @return mixed
     */
    public function getToken()
    {
        $data = $this->get($this->options['token_key']);
        if ($data)
            return $this->enc->keyDecrypt($data);

        return false;
    }

    public function deleteToken()
    {
        $this->delete($this->options['token_key']);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expire could be negative
     * @return $this
     */
    public function set($key, $value = null, $expire = 0)
    {
        $o = $this->options;

        if ($expire > 0)
            $expire += \APP_TS;
        elseif ($expire == 0)
            $expire = $o['expire'] + \APP_TS;

        parent::set($key, $value);

        setcookie($key, $value, $expire, $o['path'], $o['domain'], $o['secure'], $o['httponly']);

        return $this;
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function delete($key)
    {
        $ret = $this->set($key, '', -1);

        parent::delete($key);

        return $this;
    }

    /**
     * @return $this
     */
    public function flush()
    {
        foreach ($this->__ as $key => $val)
            $this->delete($key);

        parent::flush();

        return $this;
    }
}