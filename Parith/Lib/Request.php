<?php

/**
 * Request
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

class Request extends \Parith\Result
{
    /**
     * @param string $method
     *
     * @return bool
     */
    static public function method($method = null)
    {
        return $_SERVER['REQUEST_METHOD'] === $method;
    }

    /**
     * @return bool
     */
    static public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * @return bool
     */
    static public function isFlash()
    {
        return (bool)\preg_match('/^(Shockwave|Adobe) Flash/', $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @return bool
     */
    static public function isSSL()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * @return bool
     */
    static public function isMobile()
    {
        $ua = '(iPhone|iPod|Android|MIDP|AvantGo|BlackBerry|J2ME|Opera Mini|DoCoMo|NetFront|Nokia|PalmOS|PalmSource|portalmmm|Plucker|ReqwirelessWeb|SonyEricsson|Symbian|UP\.Browser|Windows CE|Xiino)';

        return (bool)\preg_match('/' . $ua . '/i', $_SERVER['HTTP_USER_AGENT'], $match);
    }

    static public function getHost()
    {
        return (self::isSSL() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    }

    static public function getUrl()
    {
        return self::getHost() . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return bool
     */
    static public function getClientIp()
    {
        $env = $_SERVER;// $env = $_ENV;

        if (isset($env['HTTP_X_FORWARDED_FOR'])) {
            $ips = \explode(',', $env['HTTP_X_FORWARDED_FOR'], 1);

            return $ips[0];
        } elseif (isset($env['HTTP_CLIENT_IP'])) {
            return $env['HTTP_CLIENT_IP'];
        } elseif (isset($env['REMOTE_ADDR'])) {
            return $env['REMOTE_ADDR'];
        }

        return '';
    }
}