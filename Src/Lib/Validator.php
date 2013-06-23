<?php

/**
 * Validator
 *
 *
 * Way 1:
 * \Parith\Lib\Validator::isEmail('abc@def.com');
 * \Parith\Lib\Validator::isBetweenLength('Zephyr Sun', 3 ,16);
 *
 * Way 2:
 * $validator = new \Parith\Lib\Validator($_POST);
 * $validator->email('email');
 * $validator->betweenLength('username', 3, 16);
 *
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Lib;

class Validator extends \Parith\Object
{
    public $data = array();

    public function __construct(array $data = array())
    {
        if ($data)
            $this->data = $data;
        else
            $this->data = $_POST;
    }

    public function check(array $params)
    {
        foreach ($params as $method => $args) {
            $ret = $this->$method($args);

            if (!$ret) {
                if (is_array($args))
                    $this->_last_bad = $args[0];
                else
                    $this->_last_bad = $args;

                return false;
            }
        }

        return true;
    }

    public function getLastBad()
    {
        return $this->_last_bad;
    }

    /**
     *
     * @param $name
     * @param $args
     * @return bool|mixed
     * @throws \Parith\Exception
     */
    public function __call($name, $args)
    {
        $name = 'is' . $name;

        if (method_exists($this, $name) && isset($this->data[$args[0]])) {
            $args[0] = $this->data[$args[0]];
            return \call_user_func_array(array($this, $name), $args);
        }

        return false;
    }

    /**
     * email address
     *
     * @static
     * @param $email
     * @return bool
     */
    public static function isEmail($email)
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * IPv4 or Ipv6 address
     *
     * @static
     * @param $ip
     * @return bool
     */
    public static function isIP($ip)
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * not empty
     *
     * @static
     * @param $val
     * @return bool
     */
    public static function isNotEmpty($val)
    {
        return !empty($val);
    }

    /**
     * must match equal
     *
     * @static
     * @param $val
     * @param $ref
     * @return bool
     */
    public static function isEqual($val, $ref)
    {
        return $val === $ref;
    }

    /**
     * must not match equal
     *
     * @static
     * @param $val
     * @param $ref
     * @return bool
     */
    public static function isUnequal($val, $ref)
    {
        return $val !== $ref;
    }

    /**
     * is numeric
     *
     * @static
     * @param $val
     * @return bool
     */
    public static function isNum($val)
    {
        return is_numeric($val);
    }

    /**
     * Less than or Equivalent with
     *
     * @static
     * @param $val
     * @param $ref
     * @return bool
     */
    public static function isLE($val, $ref)
    {
        return (int)$val <= (int)$ref;
    }

    /**
     * Greater than or Equivalent with
     *
     * @static
     * @param $val
     * @param $ref
     * @return bool
     */
    public static function isGE($val, $ref)
    {
        return (int)$val >= (int)$ref;
    }

    /**
     * range of number
     *
     * @static
     * @param $val
     * @param $min
     * @param $max
     * @return bool
     */
    public static function isBetween($val, $min, $max)
    {
        $val = (int)$val;
        return $val >= (int)$min && $val <= (int)$max;
    }

    /**
     *  Less than or Equivalent with length
     *
     * @static
     * @param $str
     * @param $max
     * @return bool
     */
    public static function isLELength($str, $max)
    {
        return static::isLE(mb_strlen($str), $max);
    }

    /**
     * Greater than or Equivalent with length
     *
     * @static
     * @param $str
     * @param $min
     * @return bool
     */
    public static function isGELength($str, $min)
    {
        return static::isGE(mb_strlen($str), $min);
    }

    /**
     * range of length
     *
     * @static
     * @param $str
     * @param $min
     * @param $max
     * @return bool
     */
    public static function isBetweenLength($str, $min, $max)
    {
        return static::isBetween(mb_strlen($str), $min, $max);
    }

    /**
     * regex match
     *
     * @static
     * @param $val
     * @param $regex
     * @return bool
     */
    public static function isMatch($val, $regex)
    {
        return (bool)preg_match($regex, $val);
    }

    /**
     * url
     *
     * @static
     * @param $url
     * @return bool
     */
    public static function isURL($url)
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}