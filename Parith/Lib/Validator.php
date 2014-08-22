<?php

/**
 * Validator
 *
 * e.g.:
 * $validator = new \Parith\Lib\Validator($_POST);
 * $error = $validator->checkRules(array(
 *      'email' => 'isEmail',
 *      'username' => array('isLengthBetween', 3, 8),
 * ));
 * var_dump(
 *      $error,
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

use \Parith\Result;

class Validator extends Result
{
    public $data = array();

    private $_err = array();

    public function __construct(array $data = array())
    {
        if ($data)
            $this->data = $data;
        else
            $this->data = $_POST;
    }

    public function checkRules(array $rules)
    {
        $this->_err = array();

        $ret = array();
        foreach ($rules as $field => $args) {

            $v = & $this->data[$field];

            $args = (array)$args;

            $method = $args[0];

            $args[0] = $v;

            $ret = \call_user_func_array(array($this, $method), $args);

            if ($ret) {
                $ret[$field] = $v;
            } else {
                $this->_err[] = array('method' => $method, 'arguments' => $args);
            }
        }

        if ($this->_err)
            return false;

        return $ret;
    }

    /**
     * @return array
     */
    public function getError()
    {
        return $this->_err;
    }

    /**
     * @param $name
     * @param $args
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        throw new \Exception('Rule of Validator: "' . $name . '" not found');
    }

    /**
     * email address
     *
     * @static
     * @param $email
     * @return bool
     */
    public static function email($email)
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
    public static function ip($ip)
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * not null
     *
     * @param $val
     * @return bool
     */
    public static function required($val)
    {
        return $val != null; //isset($val);
    }

    /**
     * not empty
     *
     * @static
     * @param $val
     * @return bool
     */
    public static function notEmpty($val)
    {
        return (bool)$val; //!empty($val);
    }

    /**
     * must match equal
     *
     * @static
     * @param $val
     * @param $ref
     * @return bool
     */
    public static function equal($val, $ref)
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
    public static function unequal($val, $ref)
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
    public static function num($val)
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
    public static function le($val, $ref)
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
    public static function ge($val, $ref)
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
    public static function between($val, $min, $max)
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
    public static function lengthLE($str, $max)
    {
        return static::le(mb_strlen($str), $max);
    }

    /**
     * Greater than or Equivalent with length
     *
     * @static
     * @param $str
     * @param $min
     * @return bool
     */
    public static function lengthGE($str, $min)
    {
        return static::ge(mb_strlen($str), $min);
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
    public static function lengthBetween($str, $min, $max)
    {
        return static::between(mb_strlen($str), $min, $max);
    }

    /**
     * regex match
     *
     * @static
     * @param $val
     * @param $regex
     * @return bool
     */
    public static function match($val, $regex)
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
    public static function url($url)
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}