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

namespace Parith\Data\Model;

class Database extends \Parith\Data\Model
{
    public $last_find_query = array();

    public function __construct()
    {
        parent::__construct();

        $this->options[':join'] = '';
        $this->options[':group'] = '';
    }

    public function connection($options)
    {
        return $this->ds = \Parith\Data\Source\Database::connection($options);
    }

    /**
     * @return array|int
     */
    public function getFetchMode()
    {
        if ($this->fetch_mode === parent::FETCH_OBJECT)
            return array(\PDO::FETCH_INTO, $this);

        return \PDO::FETCH_ASSOC;
    }

    /**
     * @param $query
     *          - 1 // means find $primary_key = 1
     *          - array('id' => array('<', 6), array('gender' => array('=', 'male', 'OR'), ':limit' => 5)
     * @param mixed $connection
     * @param int|array $mode
     * @return mixed
     */
    public function fetch($query, $connection = null, $mode = null)
    {
        $this->connection($connection);

        $query = $this->getFetchQuery($query);

        $mode or $mode = $this->getFetchMode();

        return $this->ds->fetch($query[0], $query[1], $mode);
    }

    /**
     * @param $query
     *          - 1 // means find $primary_key = 1
     *          - array('id' => array('<', 6), array('gender' => array('=', 'male', 'OR'), ':limit' => 5)
     *
     * @param mixed $connection
     * @param int|array $mode
     * @return mixed
     */
    public function fetchAll($query, $connection = null, $mode = null)
    {
        $this->connection($connection);

        $query = $this->getFetchQuery($query);

        $mode or $mode = $this->getFetchMode();

        return $this->ds->fetchAll($query[0], $query[1], $mode);
    }

    public function fetchCount($query = null, $connection = null)
    {
        $query or $query = $this->last_find_query;

        $query[':fields'] = 'COUNT(*)';
        $query[':limit'] = '';
        //$query[':page'] = 0;

        return $this->fetch($query, $connection, array(\PDO::FETCH_COLUMN, 0));
    }

    /**
     * @param $query
     * @return array
     */
    public function getFetchQuery($query)
    {
        is_array($query) or $query = array($this->primary_key => $query);

        $query = $this->last_find_query = $this->formatQuery($query);

        return \Parith\Data\Source\Database::selectParams($this->source($query[':source'], $query), $query[':fields'], $query[':conditions'],
            $query[':limit'], $query[':page'], $query[':order'], $query[':group'], $this->join($query[':join'], $query));
    }

    /**
     * @param array $query
     * @return array
     */
    public function formatQuery(array $query)
    {
        foreach ($query as $key => $val) {
            if (isset($this->options[$key]))
                continue;

            $query[':conditions'][$key] = $val;
        }

        $query += $this->options;

        return $query;
    }

    /**
     * @param array $data
     * @param array $query
     * @param null $connection
     * @param null $modifier
     * @return mixed
     */
    public function insert(array $data, array $query = array(), $connection = null, $modifier = null)
    {
        $query = $this->formatQuery($query);

        $this->connection($connection);

        return $this->ds->insert($this->source($query[':source'], $data), $data, $modifier);
    }

    /**
     * @param $data
     * @param array $query
     * @param null $connection
     * @return mixed
     */
    public function save(array $data = array(), array $query = array(), $connection = null)
    {
        $data = $this->resultSet($data);

        $this->connection($connection);

        $primary_value = $this->resultGet($this->primary_key);

        if ($primary_value) {
            //where
            $query[$this->primary_key] = $primary_value;
            $query = $this->formatQuery($query);

            return $this->ds->update($this->source($query[':source'], $data), $data, $query[':conditions']);
        }

        $query = $this->formatQuery($query);
        return $this->ds->insert($this->source($query[':source'], $data), $data);
    }

    /**
     * @param array $query
     * @param null $connection
     * @return mixed
     */
    public function delete($query = array(), $connection = null)
    {
        if ($query) {
            is_array($query) or $query = array($this->primary_key => $query);
        } else {
            $query = array($this->primary_key => $this->resultGet($this->primary_key));
        }

        $query = $this->formatQuery($query);

        $this->connection($connection);

        return $this->ds->delete($this->source($query[':source'], $query), $query[':conditions']);
    }

    /**
     * just overwrite it
     *
     * @param $source
     * @param $data
     * @return string returns table name
     */
    public function source($source, $data)
    {
        return $source;
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return $this->ds->lastInsertId();
    }

    /**
     *
     * \Parith\Data\Model\Database::join('comment')
     *
     * @param $join
     * @param $query
     * @return array
     * @throws \Parith\Exception
     */
    public function join($join, $query)
    {
        $ret = array();

        if ($join) {
            is_array($join) or $join = array($join);
            foreach ($join as $name) {

                $relation = &$this->relations[$name];

                if ($relation) {
                    $ret[$relation['class']->source($query[':source'], $query)] = array(
                        'on' => key($relation['key']) . '=' . current($relation['key']),
                        'type' => 'INNER JOIN',
                    );
                } else
                    throw new \Parith\Exception('Undefined relation "' . $name . '"');
            }
        }

        return $ret;
    }
}