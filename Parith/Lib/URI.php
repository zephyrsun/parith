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
     * @param string $cfg
     * @return string
     */
    static public function base($cfg = '')
    {
        $default = [
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => ''
        ];

        if ($cfg) {
            $cfg += $default;

            if ($cfg['port'])
                $cfg['port'] = ':' . $cfg['port'];
        } else {
            $cfg = $default;
        }

        return $cfg['scheme'] . '://' . $cfg['host'] . $cfg['port'] . $cfg['path'];
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

        if ($ru) {
            $cfg = ['path' => '/' . implode('/', \Parith::getEnv('route'))];
        } else {
            $cfg = '';
        }

        $base = static::base($cfg);
        return $base . $uri;
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