<?php


/**
 * URL
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

class URL
{
    /**
     * @static
     * @param null $url
     * @return string
     */
    public static function base($url = null)
    {
        $options = array(
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => '/'
        );

        if ($url) {
            $url = parse_url($url) + $options;

            if ($url['port'])
                $url['port'] = ':' . $url['port'];
        } else {
            $url = $options;
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
        if ($uri) {
            $uri = trim($uri, '/');

            \Parith\Lib\Char::isAscii($uri) or $uri = rawurlencode($uri);

        } elseif ($uri === '') {
            $uri = implode('/', \Parith\App::$uri_query);
        }

        return static::base() . $uri;
    }

    /**
     * @static
     * @param array $query
     * @return string
     */
    public static function query(array $query)
    {
        if (\defined('PHP_QUERY_RFC3986'))
            return http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return str_replace('+', '%20', http_build_query($query, '', '&'));

    }

}