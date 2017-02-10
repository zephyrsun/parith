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
     * @param string $uri
     * @return string
     */
    static public function base($uri = '')
    {
        $default = [
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => '',
            'query' => '',
        ];

        if ($uri) {
            $uri += $default;

            if ($uri['port'])
                $uri['port'] = ':' . $uri['port'];

            if ($uri['query'])
                $uri['path'] .= '?' . $uri['query'];

        } else {
            $uri = $default;
        }

        return $uri['scheme'] . '://' . $uri['host'] . $uri['port'] . $uri['path'];
    }

    /**
     * @param string $uri
     * @param string|array $query
     * @return string
     */
    static public function uri($uri = '', $query = '')
    {
        if ($uri)
            $uri = '/' . ltrim($uri, '/');

        $a = parse_url($_SERVER['REQUEST_URI']);
        $a['path'] = $uri;

        if ($query) {
            if (is_array($query))
                $query = http_build_query($query);

            $q = &$a['query'];
            $q .= ($q ? '&' : '') . $query;
        }

        return static::base($a);
    }

    static public function current()
    {
        return self::uri();
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