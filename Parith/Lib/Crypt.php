<?php

/**
 * Crypt
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

class Crypt extends Result
{
    public $options = array(
        'secret' => '!!! PLEASE CONFIG !!!',
        'algo' => 'aes-256-cbc',
    );

    public $iv_size, $cookie, $token_key, $ttl;

    /**
     * Crypt constructor.
     */
    public function __construct()
    {
        $this->setOptions(\Parith::getEnv('crypt'));

        $this->cookie = new Cookie();
        $this->token_key = $this->cookie->options['token_key'];
        $this->ttl = $this->cookie->options['expire'];

        $this->iv_size = openssl_cipher_iv_length($this->options['algo']);
    }

    public function setToken($key, $data)
    {
        $key .= \APP_TS + $this->ttl;
        return $this->cookie->set($this->token_key, $key . '.' . $this->encrypt($key, $data));
    }

    /**
     * @return array|mixed
     */
    public function getToken()
    {
        $token = $this->cookie->get($this->token_key);

        $parts = explode('.', $token, 2);
        if (count($parts) != 2)
            return false;

        $key = $parts[0];

        $data = $this->decrypt($key, $parts[1]);
        //$expire = substr($parts[0], -10);
        if (substr($key, -10) < \APP_TS) {
            return false;
        }

        return $data;
    }

    public function encrypt($key, $data)
    {
        $o = $this->options;

        $data = json_encode($data, \JSON_UNESCAPED_UNICODE);
        $data = openssl_encrypt($data, $o['algo'], $this->hash($key), OPENSSL_RAW_DATA, $this->getIv($key));

        return (new Base64Encoder())->encode($data);
    }

    public function decrypt($key, $data)
    {
        $o = $this->options;

        $data = (new Base64Encoder())->decode($data);
        $data = openssl_decrypt($data, $o['algo'], $this->hash($key), OPENSSL_RAW_DATA, $this->getIv($key));

        return json_decode($data, true); //return json_decode(rtrim($data, "\0"), true);
    }

    public function hash($key)
    {
        return hash_hmac('sha1', $key, $this->options['secret']);
    }

    public function getIv($key)
    {
        $key = hash_hmac('sha1', $this->options['secret'], $key);
        return substr($key, 0, $this->iv_size);
        //return substr(pack('h*', $key2), 0, $this->iv_size);
    }
}