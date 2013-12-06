<?php

/**
 * Validator
 *
 * e.g.:
 * $validator = new \Parith\Lib\Validator($_POST);
 * $has_error = $validator->checkRules(array(
 *      'email' => 'isEmail',
 *      'username' => array('isLengthBetween', 3, 8),
 * ));
 * var_dump(
 *      $has_error,
 *      $validator->getLastBad(),
 *      \Parith\Lib\Validator::isEmail('abc@def.com'),
 *      \Parith\Lib\Validator::isLengthBetween('hello', 3, 16)
 * );
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

use \Parith\Log;

class Validator extends \Parith\Object
{
    public $data = array(), $_error = null;

    public function __construct(array $data = array())
    {
        if ($data)
            $this->data = $data;
        else
            $this->data = $_POST;
    }

    public function checkRules(array $rules)
    {
        $this->_error = null;

        foreach ($rules as $field => $args) {
            if (isset($this->data[$field])) {

                $args = (array)$args;

                $method = $args[0];

                $args[0] = $this->data[$field];

                $ret = \call_user_func_array(array($this, $method), $args);

                if ($ret)
                    continue;

                $this->_error = array(
                    'method' => $method,
                    'arguments' => $args,
                );
            }

            return false;
        }

        return true;
    }

    public function getLastBad()
    {
        return $this->_error;
    }

    /**
     * @param $name
     * @param $args
     * @return bool|mixed
     * @throws \Parith\Exception
     */
    public function __call($name, $args)
    {
        Log::write('Rule of Validator: "' . $name . '" not found');

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
     * isset
     *
     * @param $val
     * @return bool
     */
    public static function isDefined($val)
    {
        return isset($val);
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
    public static function isLengthLE($str, $max)
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
    public static function isLengthGE($str, $min)
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
    public static function isLengthBetween($str, $min, $max)
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