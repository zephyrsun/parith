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

class URL
{
    /**
     * @param string $s
     * @return string
     */
    static public function base($s = '')
    {
        $default = [
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => '',
            'query' => '',
        ];

        if ($s) {
            $s += $default;

            if ($s['port'])
                $s['port'] = ':' . $s['port'];

            if ($s['query'])
                $s['path'] .= '?' . $s['query'];

        } else {
            $s = $default;
        }

        return $s['scheme'] . '://' . $s['host'] . $s['port'] . $s['path'];
    }

    /**
     * @param string $s
     * @param array $query
     * @return string
     */
    static public function uri($s = null, array $query = [])
    {
        $a = parse_url($_SERVER['REQUEST_URI']);

        if ($s !== null) {
            $s = '/' . ltrim($s, '/');
            $a['path'] = $s;
        }

        if ($q = &$a['query']) {
            parse_str($q, $q);
        } else {
            $q = [];
        }

        $q = self::query($query + $q);

        return static::base($a);
    }

    /**
     * @param array $query
     * @return string
     */
    static public function query(array $query)
    {
        return http_build_query($query, '', '&', \PHP_QUERY_RFC3986);
    }

}