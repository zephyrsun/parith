<?php

/**
 * Validator
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

    /**
     *
     * $validator = new \Parith\Lib\Validator($_POST);
     * $error = $validator->checkRules(array(
     *      'email' => 'email',
     *      'username' => array('length', 3, 8),
     *      'city' => array('length', 1, 50, false),
     *
     * ));
     *
     * @param array $rules
     *
     * @return array
     */
    public function checkRules(array $rules)
    {
        $this->_err = array();

        $ret = array();
        foreach ($rules as $field => $args) {

            $v = &$this->data[$field];

            $args = (array)$args;

            $method = $args[0];

            $args[0] = $v;

            $result = \call_user_func_array(array($this, $method), $args);

            if ($result || \end($args) === false) {
                $ret[$field] = $v;
            } else
                $this->_err[] = $field;
        }

        if ($this->_err)
            return array();

        return $ret;
    }

    public function getError()
    {
        return $this->_err;
    }

    public function getFirstError()
    {
        return \current($this->_err);
    }

    public function getLastError()
    {
        return \end($this->_err);
    }

    /**
     * @param $name
     * @param $args
     *
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
     *
     * @param $email
     *
     * @return bool
     */
    static public function email($email)
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * IPv4 or Ipv6 address
     *
     * @static
     *
     * @param $ip
     *
     * @return bool
     */
    static public function ip($ip)
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * not null
     *
     * @param $val
     *
     * @return bool
     */
    static public function required($val)
    {
        return $val != null; //isset($val);
    }

    /**
     * not empty
     *
     * @static
     *
     * @param $val
     *
     * @return bool
     */
    static public function notEmpty($val)
    {
        return !empty($val);
    }

    /**
     * must match equal
     *
     * @static
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    static public function equal($val, $ref)
    {
        return $val === $ref;
    }

    /**
     * must not match equal
     *
     * @static
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    static public function unequal($val, $ref)
    {
        return $val !== $ref;
    }

    /**
     * is numeric
     *
     * @static
     *
     * @param $val
     *
     * @return bool
     */
    static public function num($val)
    {
        return is_numeric($val);
    }

    /**
     * Less than or Equivalent with
     *
     * @static
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    static public function le($val, $ref)
    {
        return $val <= $ref;
    }

    /**
     * Greater than or Equivalent with
     *
     * @static
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    static public function ge($val, $ref)
    {
        return $val >= $ref;
    }

    /**
     * range of number
     *
     * @static
     *
     * @param $val
     * @param $min
     * @param $max
     *
     * @return bool
     */
    static public function between($val, $min, $max)
    {
        return $val >= $min && $val <= $max;
    }

    /**
     *  Less than or Equivalent with length
     *
     * @static
     *
     * @param $str
     * @param $max
     *
     * @return bool
     */
    static public function lengthLE($str, $max)
    {
        return static::le(mb_strlen($str), $max);
    }

    /**
     * Greater than or Equivalent with length
     *
     * @static
     *
     * @param $str
     * @param $min
     *
     * @return bool
     */
    static public function lengthGE($str, $min)
    {
        return static::ge(mb_strlen($str), $min);
    }

    /**
     * range of length
     *
     * @static
     *
     * @param $str
     * @param $min
     * @param $max
     *
     * @return bool
     */
    static public function length($str, $min, $max)
    {
        return static::between(mb_strlen($str), $min, $max);
    }

    /**
     * regex match
     *
     * @static
     *
     * @param $val
     * @param $regex
     *
     * @return bool
     */
    static public function match($val, $regex)
    {
        return (bool)preg_match($regex, $val);
    }

    /**
     * url
     *
     * @static
     *
     * @param $url
     *
     * @return bool
     */
    static public function url($url)
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}