<?php


/**
 * URL
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

class URI
{
    /**
     * @param string $opt
     * @return string
     */
    static public function base($opt = '')
    {
        $default = [
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => ''
        ];

        if ($opt) {
            $opt += $default;

            if ($opt['port'])
                $opt['port'] = ':' . $opt['port'];
        } else {
            $opt = $default;
        }

        return $opt['scheme'] . '://' . $opt['host'] . $opt['port'] . $opt['path'];
    }

    /**
     * @param string $uri
     * @param bool $ru
     * @return string
     */
    static public function uri($uri = '', $ru = false)
    {
        if ($uri)
            $uri = '/' . ltrim($uri, '/');

        $base = static::base($ru ? ['path' => '/' . $_GET['URI']] : '');
        return $base . $uri;
    }

    /**
     * @return string
     */
    static public function url()
    {
        return preg_replace('/\?.*/', '', self::uri());
    }


    /**
     * @param array $query
     * @return string
     */
    static public function query(array $query)
    {
        //if (\defined('PHP_QUERY_RFC3986'))
        return http_build_query($query, '', '&', \PHP_QUERY_RFC3986);

        //return http_build_query($query, '', '&');
    }

}