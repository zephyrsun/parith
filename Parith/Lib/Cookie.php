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
    ];

    /**
     * Cookie constructor.
     */
    public function __construct()
    {
        $this->setOptions(\Parith::getEnv('cookie'));

        $this->__ = &$_COOKIE;
    }

    public function p3p()
    {
        \header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expire could be negative
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        $o = $this->options;

        if ($expire > 0)
            $expire += \APP_TS;
        elseif ($expire == 0)
            $expire = $o['expire'] + \APP_TS;

        parent::set($key, $value);

        return setcookie($key, $value, $expire, $o['path'], $o['domain'], $o['secure'], $o['httponly']);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $ret = $this->set($key, '', -1);

        parent::delete($key);

        return $ret;
    }

    /**
     * @return void
     */
    public function flush()
    {
        foreach ($this->__ as $key => $val)
            $this->delete($key);

        parent::flush();
    }
}