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
    public $data = [];

    public $hints = [
        'email' => 'Please enter a valid email address.',
        'ip' => 'Please enter a valid IP address.',
        'required' => 'This field is required.',
        'notEmpty' => 'This field cannot be empty.',
        'equal' => 'Please enter the same value again.',
        'unequal' => 'Please enter a different value.',
        'num' => 'Please enter a valid number.',
        'max' => 'Please enter a value less than or equal to %s.',
        'min' => 'Please enter a value greater than or equal to %s.',
        'range' => 'Please enter a value between %s and %s.',
        'length' => 'Please enter a value between %s and %s characters long',
        'lengthEqual' => 'Please enter a value length equal to %s.',
        'match' => 'Please enter a valid value.',
        'url' => 'Please enter a valid URL.',
        'time' => 'Please enter a valid time.',
        'inMap' => 'Please enter a valid value.',
    ];

    public function __construct()
    {
        $this->hints = \Parith::getEnv('validator') + $this->hints;
    }

    /**
     *
     * $validator = new \Parith\Lib\Validator();
     * $error = $validator->check($_POST, [
     *      'email' => 'email',
     *      'username' => ['length', 3, 8],
     *      'city' => ['length', 1, 50, false], // false when not required
     *
     * ]);
     *
     * @param array $data
     * @param array $rules
     *
     * @return array
     */
    public function check($data, array $rules)
    {
        $data or $data = $_POST;

        $err = [];
        foreach ($rules as $field => $args) {

            $v = &$data[$field];

            $args = (array)$args;

            $method = $args[0];
            $args[0] = $v;

            $result = \call_user_func_array([$this, $method], $args);

            if ($result) {
                $this->data[$field] = $v;
            } elseif (\end($args) !== false) {
                $args[0] = $this->hints[$method];
                $err[$field] = \call_user_func_array('sprintf', $args);
            }
        }

        return $err;
    }

    public function getData()
    {
        return $this->data;
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
     * @param $email
     *
     * @return bool
     */
    public function email($email)
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * IPv4 or Ipv6 address
     *
     * @param $ip
     *
     * @return bool
     */
    public function ip($ip)
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
    public function required($val)
    {
        return $val != null; //isset($val);
    }

    /**
     * not empty
     *
     * @param $val
     *
     * @return bool
     */
    public function notEmpty($val)
    {
        return !empty($val);
    }

    /**
     * must match equal
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    public function equal($val, $ref)
    {
        return $val === $ref;
    }

    /**
     * must not match equal
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    public function unequal($val, $ref)
    {
        return $val !== $ref;
    }

    /**
     * is numeric
     *
     * @param $val
     *
     * @return bool
     */
    public function num($val)
    {
        return is_numeric($val);
    }

    /**
     * Less than or Equivalent with
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    public function max($val, $ref)
    {
        return $val <= $ref;
    }

    /**
     * Greater than or Equivalent with
     *
     * @param $val
     * @param $ref
     *
     * @return bool
     */
    public function min($val, $ref)
    {
        return $val >= $ref;
    }

    /**
     * range of number
     *
     * @param $val
     * @param $min
     * @param $max
     *
     * @return bool
     */
    public function range($val, $min, $max)
    {
        return $val >= $min && $val <= $max;
    }

    /**
     * range of length
     *
     * @param $str
     * @param $min
     * @param $max
     * @return bool
     */
    public function length($str, $min, $max)
    {
        $l = is_array($str) ? count($str) : mb_strlen($str);

        return $this->range($l, $min, $max);
    }

    /**
     * length match
     *
     * @param $str
     * @param $ref
     * @return bool
     */
    public function lengthEqual($str, $ref)
    {
        $l = is_array($str) ? count($str) : mb_strlen($str);

        return $l == $ref;
    }

    /**
     * regex match
     *
     * @param $val
     * @param $regex
     *
     * @return bool
     */
    public function match($val, $regex)
    {
        return (bool)preg_match($regex, $val);
    }

    /**
     * url
     *
     * @param $url
     *
     * @return bool
     */
    public function url($url)
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * @param $str
     * @return bool
     */
    public function time($str)
    {
        if (is_numeric($str))
            $str = date(DATE_ATOM, $str);

        return (bool)strtotime($str, \APP_TS);
    }

    /**
     * @param $key
     * @param $map
     * @return bool
     */
    public function inMap($key, $map)
    {
        return isset($map[$key]);
    }
}