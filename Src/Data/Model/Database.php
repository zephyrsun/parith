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

    public function connection($options, $query = array())
    {
        return $this->link = new \Parith\Data\Source\Database($options);
    }

    /**
     * @return array|int
     */
    public function setFetchMode()
    {
        if ($this->fetch_mode === parent::FETCH_OBJECT)
            $mode = array(\PDO::FETCH_INTO, $this);
        else
            $mode = \PDO::FETCH_ASSOC;

        return $this->link->setFetchMode($mode);
    }

    /**
     * @param $query
     * @param null $connection
     * @param array $param
     * @return mixed
     */
    public function query($query, $connection = null, array $param = array())
    {
        $this->connection($connection, $query);

        return $this->link->query($query, $param);
    }

    /**
     * @param $query
     *          - 1 // means find $primary_key = 1
     *          - array('id' => array('<', 6), array('gender' => array('=', 'male', 'OR'), ':limit' => 5)
     *          - ':source',':conditions',':fields',':order',':limit',':page' was defined in $this->options
     * @param mixed $connection
     * @param array $params
     * @return mixed
     */
    public function fetch($query, $connection = null, array $params = array())
    {
        $this->connection($connection, $query);

        if (!is_string($query)) {
            $query = $this->getFetchQuery($query, $params);
            $params += $this->link->getParams();
        }

        return $this->link->setFetchMode()->fetchAll($query, $params);
    }

    /**
     * params see fetch()
     *
     * @param $query
     * @param mixed $connection
     * @param array $params
     * @return mixed
     */
    public function fetchAll($query, $connection = null, array $params = array())
    {
        $this->connection($connection, $query);

        if (!is_string($query)) {
            $query = $this->getFetchQuery($query, $params);
            $params += $this->link->getParams();
        }

        return $this->link->setFetchMode()->fetchAll($query, $params);
    }

    /**
     * params see fetch()
     *
     * @param $query
     * @param $connection
     * @return mixed
     */
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
        $query = $this->_resultQuery($query);

        $this->last_fetch_query = $query = $this->formatQuery($query);

        $this->link
            ->table($this->source($query[':source'], $query))
            ->field($query[':fields'])
            ->limit($query[':limit'], $query[':page'])
            ->orderBy($query[':order'])
            ->groupBy($query[':group'])
            ->join($this->join($query[':join'], $query));

        return $this->link->getSelectClause();
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

                $this->link->where($val[0], $val[1], $val[2], $val[3]);

            } else {
                $this->link->where($key, $val);
            }
        }

        $query += $this->options;

        return $query;
    }

    /**
     * @param array $data
     * @param array $query
     *              - see fetch()
     * @param null $connection
     * @param null $modifier
     * @return mixed
     */
    public function insert(array $data, array $query = array(), $connection = null, $modifier = null)
    {
        $this->connection($connection, $data);

        $query = $this->formatQuery($query);

        return $this->link->table($this->source($query[':source'], $data))->insert($data, $modifier);
    }

    /**
     * @param $data
     * @param array $query
     *              - see fetch()
     * @param null $connection
     * @return mixed
     */
    public function update(array $data = array(), array $query = array(), $connection = null)
    {
        $data = $this->resultSet($data);

        foreach ((array)$this->primary_key as $k)
            $query[$k] = $this->resultGet($k);

        $this->connection($connection, $data);

        $query = $this->formatQuery($query);

        return $this->link->table($this->source($query[':source'], $data))->update($data);
    }

    /**
     * @param array $query
     *              - see fetch()
     * @param null $connection
     * @return mixed
     */
    public function delete($query = array(), $connection = null)
    {
        $query = $this->_resultQuery($query);

        $this->connection($connection, $query);

        $query = $this->formatQuery($query);

        return $this->link->table($this->source($query[':source'], $query))->delete();
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
        return $this->link->lastInsertId();
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

    private function _resultQuery($query)
    {
        if ($query) {
            if (is_array($query))
                $query = $this->resultSet($query);
            else
                $query = $this->resultSet($this->primary_key, $query);
        }

        return $query;
    }
}