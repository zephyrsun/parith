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
    public function __construct()
    {
        $this->ds = new \Parith\Data\Source\Database();
    }

    /**
     * @param $query
     *          - 1 // means find $primary_key = 1
     *          - array('id' => array('<', 6), array('gender' => array('=', 'male', 'OR'), ':limit' => 5)
     *
     * @param $connection
     * @return mixed
     */
    public function fetch($query, $connection = null)
    {
        $this->connection($connection);

        $query = $this->getFetchQuery($query);

        return $this->ds->fetch($query[0], $query[1], $this->fetch_model == parent::FETCH_OBJECT ? $this : 0);
    }

    /**
     * @param $query
     *          - 1 // means find $primary_key = 1
     *          - array('id' => array('<', 6), array('gender' => array('=', 'male', 'OR'), ':limit' => 5)
     *
     * @param $connection
     * @return mixed
     */
    public function fetchAll($query, $connection = null)
    {
        $this->connection($connection);

        $query = $this->getFetchQuery($query);

        return $this->ds->fetchAll($query[0], $query[1], $this->fetch_model == parent::FETCH_OBJECT ? $this : 0);
    }

    /**
     * @param $query
     * @return array
     */
    public function getFetchQuery($query)
    {
        is_array($query) or $query = array($this->primary_key => $query);

        $query = $this->formatQuery($query);

        return $this->ds->selectSql($this->source($query, $query[':source']), $query[':fields'], $query[':conditions'],
            $query[':limit'], $query[':page'], $query[':order']);
    }

    /**
     * @param array $query
     * @return array
     */
    public function formatQuery(array $query)
    {
        foreach ($query as $key => $val) {
            if (isset($this->defaults[$key]))
                continue;

            $query[':conditions'][$key] = $val;
        }

        $query += $this->defaults;

        return $query;
    }

    /**
     * @param $data
     * @param array $query
     * @param null $connection
     * @return mixed
     */
    public function insert($data, array $query = array(), $connection = null)
    {
        $query = $this->formatQuery($query);

        $this->connection($connection);

        return $this->ds->insert($this->source($data, $query[':source']), $data);
    }

    /**
     * @param $data
     * @param array $query
     * @param null $connection
     * @return mixed
     */
    public function save($data = null, array $query = array(), $connection = null)
    {
        if (is_array($data))
            $this->resultSet($data);

        $data = $this->resultGet();

        //where
        $query[$this->primary_key] = $this->resultGet($this->primary_key);
        $query = $this->formatQuery($query);

        $this->connection($connection);

        return $this->ds->update($this->source($data, $query[':source']), $data, $query[':conditions']);
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
        }
        else {
            $query = array($this->primary_key => $this->resultGet($this->primary_key));
        }

        $query = $this->formatQuery($query);

        $this->connection($connection);

        return $this->ds->delete($this->source($query, $query[':source']), $query[':conditions']);
    }

    /**
     * just overwrite it
     *
     * @param $data
     * @param $source
     * @return string returns table name
     */
    public function source($data, $source)
    {
        return $source;
    }
}