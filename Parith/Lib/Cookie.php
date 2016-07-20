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

class Cookie extends \Parith\Result
{
    public $options = array(
        'expire' => 7200,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'cipher' => \MCRYPT_RIJNDAEL_256,
        'mode' => \MCRYPT_MODE_CBC,
        'secret' => 'CHANGE_ME',
    )
    , $cipher
    , $key_size = 32
    , $iv_size = 32;

    /**
     * @param array $options
     *
     * @return \Parith\Lib\Cookie
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + \Parith\App::getOption('cookie') + $this->options;

        if ($this->options['cipher']) {
            $this->cipher = \mcrypt_module_open($this->options['cipher'], '', $this->options['mode'], '');
            $this->key_size = \mcrypt_enc_get_key_size($this->cipher);
            $this->iv_size = \mcrypt_enc_get_iv_size($this->cipher);
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $str = &$_COOKIE[$key];
        if ($str) {
            if ($this->cipher) {
                $arr = explode('|', $str, 2);
                return $this->decrypt(base64_decode($arr[1]), $arr[0]);
            }
        }

        return $str;
    }

    /**
     * @param string $key
     * @param mixed $str
     * @param int $expire could be negative number
     *
     * @return bool
     */
    public function set($key, $str, $expire = 0)
    {
        $options = $this->options;

        if ($expire > 0)
            $expire += \APP_TS;
        elseif ($expire == 0)
            $expire = $options['expire'] + \APP_TS;

        if ($this->cipher) {
            $str = \APP_TS . '|' . base64_encode($this->encrypt($str, \APP_TS));
        }

        $_COOKIE[$key] = $str;

        return setcookie($key, $str, $expire, $options['path'], $options['domain'], $options['secure'], $options['httponly']);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
    {
        $options = $this->options;

        return setcookie($key, '', -1, $options['path'], $options['domain'], $options['secure'], $options['httponly']);
        //  return $this->set($key, '', -1);
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
     * @param $str
     * @param $key
     * @return string
     */
    public function encrypt($str, $key)
    {
        if ($str) {
            $key = $this->hashKey($key);//$key is changed

            mcrypt_generic_init($this->cipher, $key, $this->getIv($key));
            $str = base64_encode(mcrypt_generic($this->cipher, $str));
            mcrypt_generic_deinit($this->cipher);
        }

        return $str;
    }

    /**
     * @param $str
     * @param $key
     * @return string
     */
    public function decrypt($str, $key)
    {
        if ($str) {
            $key = $this->hashKey($key);//$key is changed

            mcrypt_generic_init($this->cipher, $key, $this->getIv($key));
            $str = mdecrypt_generic($this->cipher, base64_decode($str));
            $str = rtrim($str, "\0");
            mcrypt_generic_deinit($this->cipher);
        }

        return $str;
    }

    public function hashKey($key)
    {
        $key = hash_hmac('sha1', $key, $this->options['secret']);
        return substr($key, 0, $this->key_size);
    }

    public function getIv($key)
    {
        $key2 = hash_hmac('sha1', $key, $this->options['secret']);
        return substr(pack('h*', $key . $key2), 0, $this->iv_size);
    }
}