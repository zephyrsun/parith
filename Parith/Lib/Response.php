<?php

/**
 * Response
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

use \Parith\Result;

class Response extends Result
{
    static public $protocol = 'HTTP/1.1'
    , $status_code = array(
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
    ), $mimes = array(
        'jpg' => 'image/jpeg',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bin' => 'application/octet-stream',
        'js' => 'application/javascript',
        'css' => 'text/css',
        'html' => 'text/html',
        'xml' => 'text/xml',
        'tar' => 'application/x-tar',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pdf' => 'application/pdf',
        'swf' => 'application/x-shockwave-flash',
        'zip' => 'application/x-zip-compressed',
        'gzip' => 'application/gzip',
        'woff' => 'application/x-woff',
        'svg' => 'image/svg+xml',
    );

    /**
     * @param int $code
     * @param string $msg
     *
     * @return bool
     */
    static public function httpStatus($code = 404, $msg = '')
    {
        if (!isset(static::$status_code[$code]))
            return false;

        if (!$msg)
            $msg = static::$status_code[$code];

        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : static::$protocol;
        \header($protocol . ' ' . $code . ' ' . $msg);

        return true;
    }

    /**
     * @static
     *
     * @param string $url
     * @param int $status_code
     */
    static public function redirect($url = '', $status_code = 302)
    {
        if (!\headers_sent())
            \header('Location: ' . $url, true, $status_code);
        exit(0);
    }

    /**
     * @param int $seconds
     *
     * @return void
     */
    static public function expires($seconds = 1800)
    {
        \header('Expires: ' . self::httpDate(\APP_TS + $seconds));
    }

    /**
     * @param string $etag
     *
     * @return void
     */
    static public function etag($etag)
    {
        \header('Etag: ' . $etag);
    }

    /**
     * @param int $timestamp
     *
     * @return void
     */
    static public function lastModified($timestamp = null)
    {
        \header('Last-Modified: ' . self::httpDate($timestamp));
    }

    /**
     * @param int $since_time
     * @param int $seconds
     *
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
    static public function disableCache()
    {
        \header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        self::lastModified(null);
        \header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        \header('Pragma: no-cache');
    }

    /**
     * @param string $filename
     *
     * @return void
     */
    static public function download($filename)
    {
        \header('Content-Disposition: attachment; filename="' . $filename . '"');
    }

    static public function viewFile($filename)
    {
        $ext = \Parith\Lib\Image\Basic::getExtension($filename);
        $ext = strtolower($ext);

        $ct = &self::$mimes[$ext] or $ct = 'application/octet-stream';
        header("Content-type: $ct");

        \header('Content-Disposition: inline; filename="' . $filename . '"');
    }


    /**
     * @param int $ts
     *
     * @return string
     */
    static public function httpDate($ts = null)
    {
        if (!$ts)
            $ts = \APP_TS;

        return \gmdate('D, d M Y H:i:s', $ts) . ' GMT';
    }

    /**
     * @return mixed
     */
    static public function getReferer()
    {
        if (empty($_SERVER['HTTP_REFERER']))
            return false;

        return $_SERVER['HTTP_REFERER'];
    }
}