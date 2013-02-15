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

namespace Parith\Data\Model;

class Database extends \Parith\Data\Model
{
    public $last_fetch_query = array();

    public function __construct()
    {
        parent::__construct();

        $this->options[':join'] = array();
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
    public function fetch($query, $connection = null, $mode = 0)
    {
        $this->connection($connection);

        $this->getFetchQuery($query);

        $mode or $mode = $this->getFetchMode();

        return $this->ds->fetch($mode);
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
    public function fetchAll($query, $connection = null, $mode = 0)
    {
        $this->connection($connection);

        $this->getFetchQuery($query);

        $mode or $mode = $this->getFetchMode();

        return $this->ds->fetchAll($mode);
    }

    public function fetchCount($query = null, $connection = null)
    {
        $query or $query = $this->last_fetch_query;

        $query[':fields'] = 'COUNT(*)';
        $query[':limit'] = 1;

        return $this->fetch($query, $connection, array(\PDO::FETCH_COLUMN, 0));
    }

    /**
     * @param $query
     * @return array
     */
    public function getFetchQuery($query)
    {
        is_array($query) or $query = array($this->primary_key => $query);

        $this->last_fetch_query = $query = $this->formatQuery($query);

        $this->ds
            ->table($this->source($query[':source'], $query))
            ->field($query[':fields'])
            ->limit($query[':limit'], $query[':page'])
            ->orderBy($query[':order'])
            ->groupBy($query[':group'])
            ->join($this->join($query[':join'], $query));

        return $this;
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

            if (is_array($val)) {
                is_int($key) or array_unshift($val, $key);

                $val += array('', '', '', 'AND');

                $this->ds->where($val[0], $val[1], $val[2], $val[3]);

            } else {
                $this->ds->where($key, $val);
            }
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

        return $this->ds->table($this->source($query[':source'], $data))->insert($data, $modifier);
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

        $query = $this->formatQuery($query);

        $this->ds->table($this->source($query[':source'], $data));

        $primary_value = $this->resultGet($this->primary_key);
        if ($primary_value)
            return $this->ds->where($this->primary_key, $primary_value)->update($data);

        return $this->ds->insert($data);
    }

    /**
     * @param array $query
     * @param null $connection
     * @return mixed
     */
    public function delete($query = array(), $connection = null)
    {
        if ($query)
            is_array($query) or $query = array($this->primary_key => $query);
        else
            $query = array($this->primary_key => $this->resultGet($this->primary_key));

        $query = $this->formatQuery($query);

        $this->connection($connection);

        return $this->ds->table($this->source($query[':source'], $query))->delete();
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

                $relation = & $this->relations[$name];

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