<?php


/**
 * URL
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
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
        $options = array(
            'scheme' => 'http',
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            'port' => '',
            'path' => '/'
        );

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
    static public function link($uri = '')
    {
        if ($uri) {
            if (!Char::isAscii($uri))
                $uri = rawurlencode($uri);
        } else {
            $uri = $_SERVER['REQUEST_URI']; //implode('/', \Parith\App::$query);
        }

        return static::base() . trim($uri, '/');
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