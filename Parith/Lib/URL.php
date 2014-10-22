<?php


/**
 * URL
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

use Parith\Router;

class URL
{
    /**
     * @static
     *
     * @param null $url
     *
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
     *
     * @param string $url
     *
     * @return string
     */
    public static function link($url = '')
    {
        if ($url) {
            $url = trim($url, '/');

            Char::isAscii($url) or $url = rawurlencode($url);

        } elseif ($url === '') {
            $url = implode('/', \Parith\App::$query); //CONTROLLER . '/' . ACTION;
        }

        return static::base() . $url;
    }

    /**
     * @static
     *
     * @param array $query
     *
     * @return string
     */
    public static function query(array $query)
    {
        if (\defined('PHP_QUERY_RFC3986'))
            return http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return str_replace('+', '%20', http_build_query($query, '', '&'));

    }

}