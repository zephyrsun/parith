<?php

/**
 * Data Model
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

abstract class Model extends \Parith\Result
{
    CONST
        FETCH_OBJECT = 1,
        FETCH_ARRAY = 2;

    public $defaults = array(
        '$source' => '',
        '$conditions' => '',
        '$fields' => '*',
        '$order' => '',
        '$limit' => '',
        '$page' => '',
    ), $ds, $fetch_model = self::FETCH_ARRAY, $config_file = 'Model', $primary_key = 'id';

    public static $static_methods = array(
        'find' => 'fetch',
        'findAll' => 'fetchAll',
        'create' => 'insert',
        'update' => 'save',
        'remove' => 'delete',
    );

    abstract public function fetch($query, $connection = null);

    abstract public function insert($data, array $query = array(), $connection = null);

    abstract public function save($data = null, array $query = array(), $connection = null);

    abstract public function delete($query = array(), $connection = null);

    /**
     * @static
     * @param string $name
     * @param array $arguments
     * @return bool|mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $obj = parent::getInstance();

        if (isset(static::$static_methods[$name]))
            return \call_user_func_array(array($obj, static::$static_methods[$name]), $arguments);

        return false;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function connection($name)
    {
        return $this->ds->connect($this->getServer($name));
    }

    /**
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function getServer($name, array $options = array())
    {
        return $this->ds->getServer($this->config_file, $name, $options);
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