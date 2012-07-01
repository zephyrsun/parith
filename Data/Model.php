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

    public $options = array(
        ':source' => '',
        ':conditions' => '',
        ':fields' => '*',
        ':order' => '',
        ':limit' => '',
        ':page' => '',
    ),

        $ds,
        $fetch_model = self::FETCH_ARRAY,
        $config_file = 'Model',
        $primary_key = 'id',

        $has_one = array(),
        $belongs_to = array(),
        $has_many = array();

    public static $method_alias = array(
        'find' => 'fetch',
        'findAll' => 'fetchAll',
        'create' => 'insert',
        'update' => 'save',
        'remove' => 'delete',
    );

    abstract public function fetch($query, $connection = null);

    abstract public function insert(array $data, array $query = array(), $connection = null);

    abstract public function save(array $data = array(), array $query = array(), $connection = null);

    abstract public function delete($query = array(), $connection = null);

    /**
     * @static
     * @param string $name
     * @param array $arguments
     * @return bool|mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (isset(static::$method_alias[$name]))
            return \call_user_func_array(array(parent::factory(), static::$method_alias[$name]), $arguments);

        return false;
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        if (isset(static::$method_alias[$name]))
            return \call_user_func_array(array(parent::factory(), static::$method_alias[$name]), $arguments);

        return false;
    }

    /**
     * an Overwrite example:
     *
     * public function connection($cfg_id)
     * {
     *      $servers = array (
     *          1 => array('host' => '127.0.0.1', 'port' => 11211),
     *          2 => array('host' => '127.0.0.1', 'port' => 11212),
     *      );
     *
     *      return $this->ds = \Parith\Data\Source\Database::connection($servers[$cfg_id]);
     * }
     *
     * @param $connection
     * @return mixed
     */
    abstract public function connection($connection);

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