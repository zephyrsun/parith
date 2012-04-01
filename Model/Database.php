<?php

/**
 * Database Model
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

namespace Parith\Model;

abstract class Database extends Model
{
    public $primary = 'id';

    public static function connection()
    {
        return \Parith\Object::getInstance('\Parith\DataSource\Database');
    }

    public static function find($params = array())
    {
    }

    public static function create()
    {

    }

    public static function update()
    {

    }

    public static function insert()
    {

    }
}