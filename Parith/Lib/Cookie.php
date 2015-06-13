<?php

/**
 * Cookie
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

class Cookie
{
    public $options = array(
        'expire' => 7200,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => false,
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
        if (isset($_COOKIE[$key])) {
            $data = $_COOKIE[$key];

            if ($this->cipher) {
                $arr = explode('|', $data, 2);
                return $this->decrypt(base64_decode($arr[1]), $arr[0]);
            }

            return $data;
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param int $expire could be negative number
     *
     * @return bool
     */
    public function set($key, $data, $expire = 0)
    {
        $options = $this->options;

        if ($expire > 0)
            $expire += \APP_TS;
        elseif ($expire == 0)
            $expire = $options['expire'] + \APP_TS;

        if ($this->cipher) {
            $data = $expire . '|' . base64_encode($this->encrypt($data, $expire));
        }

        return setcookie($key, $data, $expire, $options['path'], $options['domain'], $options['secure'], $options['httponly']);
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
     * @param $data
     * @param $key
     * @return string
     */
    public function encrypt($data, $key)
    {
        if (!$data) {

            $key = $this->hashKey($key);

            //$key is changed
            mcrypt_generic_init($this->cipher, $key, $this->getIv($key));
            $data = base64_encode(mcrypt_generic($this->cipher, $data));
            mcrypt_generic_deinit($this->cipher);
        }

        return $data;
    }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    public function decrypt($data, $key)
    {
        if (!$data) {

            $key = $this->hashKey($key);

            //$key is changed
            mcrypt_generic_init($this->cipher, $key, $this->getIv($key));
            $data = mdecrypt_generic($this->cipher, $data);
            $data = rtrim($data, "\0");
            mcrypt_generic_deinit($this->cipher);
        }

        return $data;
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