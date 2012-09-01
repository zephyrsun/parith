<?php

/**
 * Response
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith\Lib;

class Response extends \Parith\Object
{
    public static $protocol = 'HTTP/1.1',
        $status_code = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * @param int $code
     * @param string $msg
     * @return bool
     */
    public static function httpStatus($code = 404, $msg = '')
    {
        if (!isset(static::$status_code[$code]))
            return false;

        $msg or $msg = static::$status_code[$code];

        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : static::$protocol;
        \header($protocol . ' ' . $code . ' ' . $msg);

        return true;
    }

    /**
     * @static
     * @param string $uri
     * @param int $status_code
     */
    public static function redirect($uri = '', $status_code = 302)
    {
        \headers_sent() or \header('Location: ' . $uri, true, $status_code);
        exit(1);
    }

    /**
     * @param int $seconds
     * @return void
     */
    public static function expires($seconds = 1800)
    {
        \header('Expires: ' . self::httpDate(APP_TS + $seconds));
    }

    /**
     * @param string $etag
     * @return void
     */
    public static function etag($etag)
    {
        \header('Etag: ' . $etag);
    }

    /**
     * @param int $timestamp
     * @return void
     */
    public static function lastModified($timestamp = null)
    {
        \header('Last-Modified: ' . self::httpDate($timestamp));
    }

    /**
     * @param int $since_time
     * @param int $seconds
     * @return void
     */
    public function cache($since_time, $seconds = 1800)
    {
        \header('Date: ' . self::httpDate());
        self::lastModified($since_time);
        self::expires($seconds);
        \header('Cache-Control: public, max-age=' . $seconds);
        \header('Pragma: cache');
    }

    /**
     * @return void
     */
    public static function disableCache()
    {
        \header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        self::lastModified(null);
        \header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        \header('Pragma: no-cache');
    }

    /**
     * @param string $filename
     * @return void
     */
    public static function download($filename)
    {
        \header('Content-Disposition: attachment; filename="' . $filename . '"');
    }

    /**
     * @param int $timestamp
     * @return string
     */
    public static function httpDate($timestamp = null)
    {
        $timestamp or $timestamp = APP_TS;

        return \gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
    }

    /**
     * @return mixed
     */
    public static function getReferer()
    {
        return !empty($_SERVER['HTTP_REFERER']) ? false : $_SERVER['HTTP_REFERER'];
    }
}