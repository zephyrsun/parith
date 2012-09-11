<?php

/**
 * XXTEA
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

class XXTEA extends \Parith\Object
{
    protected $key = array(1234567890, 1234567890, 1234567890, 1234567890), $delta = 0x9E3779B9;

    /**
     * @param string $key
     * @return \Parith\Lib\XXTEA
     */
    public function __construct($key = '')
    {
        if ($key)
            $this->setKey($key);
    }

    /**
     * @param string|array $key
     * @return \Parith\Lib\XXTEA
     */
    public function setKey($key)
    {
        if (\is_string($key))
            $key = self::_str2long($key, false);
        elseif (!\is_array($key))
            throw new \Parith\Exception('XXTEA key must be String or Array');

        if (\count($key) < 4)
            $this->key = $key + $this->key;

        return $this;
    }

    /**
     * @param string|array $var
     * @return string|array
     */
    public function encrypt($var)
    {
        if (\is_scalar($var))
            return $this->_encryptString($var);
        elseif (\is_array($var))
            return $this->_encryptArray($var);

        throw new \Parith\Exception('encrypt data must be String or Array');
    }

    /**
     * @param string $str
     * @return string
     */
    public function _encryptString($str)
    {
        if (!$str)
            return false;

        return self::_long2str($this->_encryptArray(self::_str2long($str, true)), false);
    }

    /**
     * @param  string|array $v
     * @return string|array
     */
    public function _encryptArray($v)
    {
        $n = \count($v) - 1;
        $z = $v[$n];
        $q = \floor(6 + 52 / ($n + 1));
        $sum = 0;
        while (0 < $q--) {
            $sum = self::_int32($sum + $this->delta);
            $e = $sum >> 2 & 3;
            for ($p = 0; $p < $n; $p++) {
                $y = $v[$p + 1];
                $mx = self::_int32((($z >> 5 & 0x07FFFFFF) ^ $y << 2) + (($y >> 3 & 0x1FFFFFFF) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($this->key[$p & 3 ^ $e] ^ $z));
                $z = $v[$p] = self::_int32($v[$p] + $mx);
            }
            $y = $v[0];
            $mx = self::_int32((($z >> 5 & 0x07FFFFFF) ^ $y << 2) + (($y >> 3 & 0x1FFFFFFF) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($this->key[$p & 3 ^ $e] ^ $z));
            $z = $v[$n] = self::_int32($v[$n] + $mx);
        }

        return $v;
    }

    /**
     * @param string|array $var
     * @return string|array|bool
     */
    public function decrypt($var)
    {
        if (\is_string($var))
            return $this->_decryptString($var);
        elseif (\is_array($var))
            return $this->_decryptArray($var);

        return false;
    }

    /**
     * @param string $str
     * @return string
     */
    public function _decryptString($str)
    {
        if (!$str)
            return false;

        return self::_long2str($this->_decryptArray(self::_str2long($str, false)), true);
    }

    /**
     * @param string|array $v
     * @return string|array
     */
    public function _decryptArray($v)
    {
        $n = \count($v) - 1;
        $y = $v[0];
        $q = \floor(6 + 52 / ($n + 1));
        $sum = self::_int32($q * $this->delta);
        while ($sum != 0) {
            $e = $sum >> 2 & 3;
            for ($p = $n; $p > 0; $p--) {
                $z = $v[$p - 1];
                $mx = self::_int32((($z >> 5 & 0x07FFFFFF) ^ $y << 2) + (($y >> 3 & 0x1FFFFFFF) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($this->key[$p & 3 ^ $e] ^ $z));
                $y = $v[$p] = self::_int32($v[$p] - $mx);
            }
            $z = $v[$n];
            $mx = self::_int32((($z >> 5 & 0x07FFFFFF) ^ $y << 2) + (($y >> 3 & 0x1FFFFFFF) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($this->key[$p & 3 ^ $e] ^ $z));
            $y = $v[0] = self::_int32($v[0] - $mx);
            $sum = self::_int32($sum - $this->delta);
        }

        return $v;
    }

    /**
     * @param string $s
     * @param bool $w
     * @return array
     */
    private static function _str2long($s, $w = false)
    {
        $v = \array_values(\unpack('V*', $s . \str_repeat("\0", (4 - \strlen($s) % 4) & 3)));
        if ($w)
            $v[] = \strlen($s);

        return $v;
    }

    /**
     * @param mixed $v
     * @param bool $w
     * @return string
     */
    private static function _long2str($v, $w = false)
    {
        $len = \count($v);
        $s = '';
        for ($i = 0; $i < $len; $i++)
            $s .= pack('V', $v[$i]);

        if ($w)
            return substr($s, 0, $v[$len - 1]);

        return $s;
    }

    /**
     * @param int $n
     * @return int
     */
    private static function _int32($n)
    {
        while ($n >= 2147483648) $n -= 4294967296;
        while ($n <= -2147483649) $n += 4294967296;

        return (int)$n;
    }
}