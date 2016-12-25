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
     * @static
     *
     * @param null $uri
     *
     * @return string
     */
    static public function base($uri = null)
    {
        $options = [
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => '/'
        ];

        if ($uri) {
            $uri = parse_url($uri) + $options;

            if ($uri['port'])
                $uri['port'] = ':' . $uri['port'];
        } else {
            $uri = $options;
        }

        return $uri['scheme'] . '://' . $uri['host'] . $uri['port'] . $uri['path'];
    }

    /**
     * @static
     *
     * @param string $uri
     *
     * @return string
     */
    static public function uri($uri = '')
    {
        $uri or $uri = implode('/', \Parith::getOption('route'));

        return trim(static::base() . $uri, '/');
    }

    static public function url()
    {
        return preg_replace('/\?.*/', '', self::uri());
    }

    /**
     * @static
     *
     * @param array $query
     *
     * @return string
     */
    static public function query(array $query)
    {
        //if (\defined('PHP_QUERY_RFC3986'))
        return http_build_query($query, '', '&', \PHP_QUERY_RFC3986);

        //return http_build_query($query, '', '&');
    }

}