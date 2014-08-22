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

use \Parith\Data\Model;

class Database extends Model
{
    public $last_fetch_query = array();
    public $per_page = 10;

    public $db_name = '';
    public $table_name = '';

    private $_fetch_mode = null;

    public function __construct()
    {
        parent::__construct();

        if ($this->fetch_mode === parent::FETCH_OBJECT)
            $this->_fetch_mode = array(\PDO::FETCH_INTO, $this);
        else
            $this->_fetch_mode = \PDO::FETCH_ASSOC;

        $this->options[':join'] = array();
        $this->options[':group'] = '';
        $this->options[':having'] = '';
        $this->options[':source'] = $this->table_name;
    }

    public function connection($query = array())
    {
        return $this->link = new \Parith\Data\Source\Database(array(
            'db_name' => $this->db_name
        ));
    }

    /**
     * @return array|int
     */
    public function setFetchMode($mode)
    {
        $mode or $mode = $this->_fetch_mode;

        return $this->link->setFetchMode($mode);
    }

    /**
     * @param $query
     * @param array $param
     * @return mixed
     */
    public function query($query, array $param = array())
    {
        $this->connection($query);

        return $this->link->query($query, $param);
    }

    /**
     * @param $query
     *          - 1 // means find $primary_key = 1
     *          - array('id' => array('<', 6), array('gender' => array('=', 'male', 'OR'), ':limit' => 5)
     *          - ':source',':conditions',':fields',':order',':limit',':page' was defined in $this->options
     * @param array $params
     * @param array|null $mode
     * @return mixed
     */
    public function fetch($query, array $params = array(), $mode = null)
    {
        $this->connection($query);

        if (!is_string($query)) {
            $query = $this->getFetchQuery($query, $params);
            $params += $this->link->getParams();
        }

        return $this->setFetchMode($mode)->fetch($query, $params);
    }

    /**
     * params see fetch()
     *
     * @param $query
     * @param array $params
     * @param array|null $mode
     * @return mixed
     */
    public function fetchAll($query, array $params = array(), $mode = null)
    {
        $this->connection($query);

        if (!is_string($query)) {
            $query = $this->getFetchQuery($query, $params);
            $params += $this->link->getParams();
        }

        return $this->setFetchMode($mode)->fetchAll($query, $params);
    }

    /**
     * params see fetch()
     *
     * @param $query
     * @return mixed
     */
    public function fetchCount($query = null)
    {
        $query or $query = $this->last_fetch_query;

        $query[':fields'] = 'COUNT(*)';
        $query[':limit'] = 1;

        unset($query[':page']);

        return $this->fetch($query, array(), array(\PDO::FETCH_COLUMN, 0));
    }

    /**
     * @param $query
     * @return array
     */
    public function getFetchQuery($query)
    {
        //$query = $this->_resultQuery($query);

        $this->last_fetch_query = $query = $this->formatQuery($query);

        $this->table($query[':source'], $query);

        $this->link
            ->field($query[':fields'])
            ->limit($query[':limit'], $query[':page'])
            ->groupBy($query[':group'])
            ->having($query[':having'])
            ->orderBy($query[':order'])
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
     * overwrite it, sharding etc.
     *
     * @param $table
     * @param $data
     */
    public function table($table, $data)
    {
        $this->link->table($table);
    }


    /**
     * @param array $data
     * @param array $query
     *              - see fetch()
     * @param null $modifier
     * @return mixed
     */
    public function insert(array $data, array $query = array(), $modifier = null)
    {
        return $this->prepare($data, $query)->insert($data, $modifier);
    }

    /**
     * @param $data
     * @param array $query
     *              - see fetch()
     * @param bool $auto
     * @return mixed
     */
    public function update(array $data = array(), array $query = array(), $auto = false)
    {
        $data = $this->resultSet($data);

        foreach ((array)$this->primary_key as $k)
            $query[$k] = $this->resultGet($k);

        $this->prepare($data, $query);

        if ($auto && !$this->link->getWhere())
            return $this->link->insert($data);

        return $this->link->update($data);
    }

    /**
     * @param $data
     * @param array $query
     *              - see fetch()
     * @return mixed
     */
    public function save(array $data = array(), array $query = array())
    {
        return $this->update($data, $query, true);
    }

    /**
     * @param array $query
     *              - see fetch()
     * @return mixed
     */
    public function delete($query = array())
    {
        $query = $this->_resultQuery($query);

        return $this->prepare($query, $query)->delete();
    }

    /**
     * @param $data
     * @param $query
     * @return \Parith\Data\Source\Database
     */
    protected function prepare($data, $query)
    {
        $this->connection($data);

        $query = $this->formatQuery($query);

        $this->table($query[':source'], $data);

        return $this->link;
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return $this->link->lastInsertId();
    }

    /**
     * @return string
     */
    public function getLastSql()
    {
        return $this->link->getLastSql();
    }

    /**
     *
     * \Parith\Data\Model\Database::join('comment')
     *
     * @param $join
     * @param $query
     * @return array
     * @throws \Exception
     */
    public function join($join, $query)
    {
        $ret = array();

        if ($join) {
            $join = (array)$join;
            foreach ($join as $key => $name) {

                if (is_string($key)) {
                    $ret[$key] = & $name;
                    continue;
                }

                $relation = & $this->relations[$name];

                if ($relation) {
                    $ret[$relation['class']->source($query[':source'], $query)] = array(
                        'on' => key($relation['key']) . '=' . current($relation['key']),
                        'type' => 'INNER JOIN',
                    );
                } else
                    throw new \Exception('Undefined relation "' . $name . '"');
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
