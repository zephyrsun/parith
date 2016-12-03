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
        'expire' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ];

    /**
     * Cookie constructor.
     */
    public function __construct()
    {
        $this->options = \Parith\App::getOption('cookie') + $this->options;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $str = &$_COOKIE[$key];

        return $str;
    }

    /**
     * @param string $key
     * @param mixed $str
     * @param int $expire could be negative
     * @return bool
     */
    public function set($key, $str, $expire = 0)
    {
        $o = $this->options;

        if ($expire > 0)
            $expire += \APP_TS;
        elseif ($expire == 0)
            $expire = $o['expire'] + \APP_TS;

        $_COOKIE[$key] = $str;

        return setcookie($key, $str, $expire, $o['path'], $o['domain'], $o['secure'], $o['httponly']);
    }

    /**
     * @param $key
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

    public function setJWT($id, array $data)
    {
        return $this->set('__token__', (new JWTAuth())->sign($data, $id));
    }

    public function getJWT()
    {
        $jwt = new JWTAuth();
        $data = $jwt->authenticate($this->get('__token__'));
        if ($data && $data['exp'] < \APP_TS) {
            //refresh
            $data = $jwt->makePayload($data['sub'], $data);
            $jwt->sign($data, 0);
        }

        return $data;
    }
}