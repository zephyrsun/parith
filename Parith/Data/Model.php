<?php

/**
 * Data Model
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Data;

use \Parith\Result;

abstract class Model extends Result
{
    CONST
        FETCH_ARRAY = 0,
        FETCH_OBJECT = 1;

    public $options = array(
        ':source' => '',
        ':conditions' => '',
        ':fields' => '*',
        ':order' => '',
        ':limit' => 0,
        ':page' => 0,
    )
    , $link
    , $fetch_mode = self::FETCH_ARRAY
    , $primary_key = 'id'

        /**
         * array(
         * 'comment' => array('type' => 'has_many', 'key' => array('comment_id' => 'id')),
         * );
         * @var array
         *          - type: belongs_to, has_one, has_many
         */
    , $relations = array();

    public static $method_alias = array(
        'find' => 'fetch',
        'findAll' => 'fetchAll',
        'create' => 'insert',
        'modify' => 'update',
        'remove' => 'delete',
    )
    , $model_dir = '';

    private static $_relation_types = array(
        'belongs_to' => 1,
        'has_one' => 1,
        'has_many' => 1,
    );


    abstract public function fetch($query);

    abstract public function insert(array $data, array $query = array());

    abstract public function update(array $data = array(), array $query = array());

    abstract public function delete($query = array());

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
     * @param array $query
     * @return mixed
     */
    abstract public function connection($query = array());

    public function __construct()
    {
        $this->initRelations();
    }

    /**
     * @static
     * @param string $name
     * @param array $args
     * @return bool|mixed
     */
    public static function __callStatic($name, $args)
    {
        if (isset(static::$method_alias[$name]))
            return \call_user_func_array(array(parent::singleton(), static::$method_alias[$name]), $args);

        return false;
    }

    /**
     * @param $name
     * @param $args
     * @return bool|mixed
     */
    public function __call($name, $args)
    {
        if (isset(static::$method_alias[$name]))
            return \call_user_func_array(array(parent::singleton(), static::$method_alias[$name]), $args);

        return false;
    }

    /**
     * @static
     * @return string
     */
    public static function getModelDir()
    {
        static::$model_dir or static::$model_dir = preg_replace('/[^\\\\]+$/', '', \get_called_class());

        return static::$model_dir;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function initRelations()
    {
        foreach ($this->relations as $name => &$config) {
            if (is_array($config)) {
                if (isset(self::$_relation_types[$config['type']])) {

                    $class = static::getModelDir() . \ucfirst($name);
                    $config['class'] = new $class();

                    isset($config['key']) or $config['key'] = array($name . '_id' => $config['class']->primary_key);

                } else
                    throw new \Exception('Error type of relation "' . $name . '"');
            } else
                throw new \Exception('Config of relation "' . $name . '" must be an array');
        }

        return $this->relations;
    }
}