<?php


/**
 * Url
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith\Lib;

class Url
{
    /**
     * @static
     * @param null $url
     * @return string
     */
    public static function base($url = null)
    {
        $defaults = array(
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => '/'
        );

        if ($url) {
            $url = parse_url($url) + $defaults;

            if ($url['port'])
                $url['port'] = ':' . $url['port'];
        }
        else {
            $url = $defaults;
        }

        return $url['scheme'] . '://' . $url['host'] . $url['port'] . $url['path'];
    }

    /**
     * @static
     * @param string $uri
     * @return string
     */
    public static function link($uri = '')
    {
        $uri = trim($uri, '/');
        if (!\Parith\Lib\Char::isAscii($uri))
            $uri = rawurlencode($uri);

        return static::base() . $uri;
    }

}