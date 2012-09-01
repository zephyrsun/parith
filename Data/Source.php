<?php

/**
 * Data Source
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

namespace Parith\Data;

abstract class Source
{
    public $configs = array();

    public static $options = array('host' => '127.0.0.1', 'port' => 0);

    protected $link;

    private static $_instances = array();

    /**
     * disconnect from server
     * @abstract
     */
    abstract public function close();

    /**
     * @abstract
     * @param array $options
     * @return mixed
     */
    abstract public function connect(array $options);

    public function __construct(array $options = array())
    {
        if ($options)
            $this->connect($options);
    }

    /**
     * singleton
     *
     * @static
     * @param array $options
     * @return \get_called_class
     */
    public static function connection($options)
    {
        return \Parith\Object::getInstance(\get_called_class(), func_get_args(), static::instanceKey($options));
    }

    /**
     * @param $options
     * @return string
     */
    public static function instanceKey($options)
    {
        if (is_array($options))
            return $options['host'] . ':' . $options['port'];

        return $options;
    }

    /**
     * an Overwrite example:
     *
     * public static function option($cfg_id)
     * {
     *      $servers = array (
     *          1 => array('host' => '127.0.0.1', 'port' => 11211),
     *          2 => array('host' => '127.0.0.1', 'port' => 11212),
     *      );
     *
     *      return parent::option($servers[$cfg_id]);
     * }
     *
     * @static
     * @param array $options
     * @return array|bool
     * @throws \Parith\Exception
     */
    public static function option($options)
    {
        if (is_array($options))
            return $options + static::$options;

        throw new \Parith\Exception('options must be an Array');

        return false;
    }

    /**
     * @param $var
     * @return string
     */
    public static function encode($var)
    {
        return \json_encode($var);
    }

    /**
     * @param $var
     * @return mixed
     */
    public static function decode($var)
    {
        return $var ? \json_decode($var, true) : $var;
    }
}