<?php

/**
 * JWTAuth
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

class Base64Encoder
{
    public function encode($data)
    {
        //return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        return strtr(base64_encode($data), '+/', '-_');
    }

    public function decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}