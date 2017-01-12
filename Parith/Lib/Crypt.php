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
        'secret' => 'PLEASE_CHANGE_ME',
        'cipher' => \MCRYPT_RIJNDAEL_256,
        'mode' => \MCRYPT_MODE_CBC,
        'ttl' => 43200,
    );

    public $res, $key_size = 0, $iv_size = 0;

    public $cookie, $token_key, $encoder;

    /**
     * Crypt constructor.
     */
    public function __construct()
    {
        $this->setOptions(\Parith::getEnv('crypt'));

        $this->cookie = new Cookie();
        $this->token_key = $this->cookie->options['token_key'];

        $this->encoder = new Base64Encoder();
    }

    public function setOptions($options)
    {
        parent::setOptions($options);

        $this->res = \mcrypt_module_open($this->options['cipher'], '', $this->options['mode'], '');
        $this->key_size = \mcrypt_enc_get_key_size($this->res);
        $this->iv_size = \mcrypt_enc_get_iv_size($this->res);
    }

    public function setToken($key, $data)
    {
        $key .= \APP_TS + $this->options['ttl'];
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
        $data = json_encode($data, \JSON_UNESCAPED_UNICODE);

        $key = $this->hashKey($key);//$key is changed

        mcrypt_generic_init($this->res, $key, $this->getIv($key));
        $data = $this->encoder->encode(mcrypt_generic($this->res, $data));
        mcrypt_generic_deinit($this->res);

        return $data;
    }

    public function decrypt($key, $data)
    {
        $key = $this->hashKey($key);

        mcrypt_generic_init($this->res, $key, $this->getIv($key));
        $data = mdecrypt_generic($this->res, $this->encoder->decode($data));
        mcrypt_generic_deinit($this->res);

        return json_decode(rtrim($data, "\0"), true);
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