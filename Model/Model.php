<?php

/**
 * Model
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

abstract class Model extends \Parith\Result
{
    const
        DML_INSERT = 'INSERT INTO', DML_INSERT_IGNORE = 'INSERT IGNORE INTO', DML_REPLACE = 'REPLACE INTO',
        HAS_ONE = 0, HAS_MANY = 1, BELONGS_TO = 0;

    public
        $db, $table = '', $primary = 'id', $alias = '',
        $qc = array(), $qp = array(),
        $result_class = '\Parith\Model\ModelResult';

    public static $sql_clauses = array(
        'select' => '*', 'join' => '', 'where' => '',
        'orderby' => '', 'groupby' => '', 'limit' => '', 'offset' => '',
    );

    private $_dml_insert = self::DML_INSERT, $_rels = array();

    /**
     * @return \Parith\Model\Model
     */
    public function __construct()
    {
        $this->clear();

        $this->_initRelation();

        // setup ModelResult
        //$result = new $this->result_class($this);
        //$this->db->setFetchMode($result);
    }

    /**
     * @return array
     */
    public function relations()
    {
        return array();
    }

    /**
     * @param mixed $id
     * @param array $query
     * @return mixed
     */
    public function find($id = null, array $query = array())
    {
        if (\is_array($id))
            return $this->where('`' . $this->primary . '` IN (' . \implode(', ', $id) . ')')->findAll($query);

        return $this->where($this->primary, $id)->findOne($query);
    }

    /**
     * @param array $query
     * @return mixed
     */
    public function findOne(array $query = array())
    {
        $this->limit(1);

        $row = $this->db->fetch($this->_findQuery($query), $this->qp);

        $this->clear();

        return $row ? is_object($row) ? clone $row : $row : false;
    }

    /**
     * @param array $query
     * @return array
     */
    public function findAll(array $query = array())
    {
        $this->db->query($this->_findQuery($query), $this->qp);
        $stmt = $this->db->getStatement();

        $ret = array();
        while ($row = $stmt->fetch())
            $row and $ret[] = clone $row;

        $this->clear();

        return $ret;
    }

    /**
     * @param array $var
     * @return bool
     */
    public function save(array $var = array())
    {
        return $this->resultGet($this->primary) ? $this->update($var) : $this->insert($var);
    }

    /**
     * @param array $var
     * @return bool
     */
    public function update(array $var = array())
    {
        $var and $this->resultSet($var); # push new data into class
        $var = $this->resultGet();

        $value = $comma = '';
        foreach ($var as $k => $v)
        {
            $col = ':qpu_' . $k;

            $value .= $comma . '`' . $k . '`=' . $col;

            $this->qp[$col] = $v;

            $comma = ', ';
        }

        $this->where($this->primary, $this->resultGet($this->primary));

        $sql = 'UPDATE ' . $this->getTable(false) . ' SET ' . $value . $this->qc['where'] . ';';

        $this->db->query($sql, $this->qp);

        $this->clear();

        return $this->db->rowCount();
    }

    /**
     * @param array $var
     * @return bool
     */
    public function insert(array $var = array())
    {
        $var and $this->resultSet($var); # push new data into class
        $var = $this->resultGet();

        $field = $value = $comma = '';
        foreach ($var as $k => $v)
        {
            $col = ':qpi_' . $k;

            $field .= $comma . '`' . $k . '`';
            $value .= $comma . $col;

            $this->qp[$col] = $v;

            $comma = ', ';
        }

        $sql = $this->_dml_insert . ' ' . $this->getTable(false) . ' (' . $field . ') VALUES (' . $value . ');';

        $ret = $this->db->query($sql, $this->qp);

        $this->clear();

        return $ret ? $this->db->lastInsertId() : false;
    }

    /**
     * @param mixed $id
     * @return bool
     */
    public function delete($id = null)
    {
        $id === null and $id = $this->resultGet($this->primary);

        $this->where($this->primary, $id);

        $sql = 'DELETE FROM ' . $this->getTable(false) . $this->qc['where'] . ';';

        $ret = $this->db->query($sql, $this->qp);

        foreach ($this->getRelation() as $name => $ref)
        {
            if ($ref['cascade']) {
                $model = $ref[1]::getInstance();
                $key = $ref['reference'] or $key = $model->primary;
                $model->where($key, $id)->delete();
            }
        }

        $this->clear();

        return $ret;
    }

    /**
     * @param mixed $id
     * @param mixed $ref
     * @return mixed
     */
    public function findRelation($id = null, $ref = null)
    {
        $id === null and $id = $this->resultGet($this->primary);

        $refs = $this->getRelation();

        if (is_string($ref))
            return $this->_findRelation($ref, $refs, $id);

        $ret = array();
        foreach (\array_keys($refs) as $ref)
            $ret[$ref] = $this->_findRelation($ref, $refs, $id);

        return $ret;
    }

    /**
     * @param string $name
     * @param mixed $refs
     * @param mixed $id
     * @return mixed
     */
    private function _findRelation($name, $refs, $id)
    {
        $ref = &$refs[$name];
        if ($ref === null)
            return false;

        $model = $ref[1]::getInstance();
        $key = $ref['reference'] or $key = $model->primary;
        $model->where($key, $id);

        // relation type
        return $ref[0] === 1 ? $model->findAll() : $model->findOne();
    }

    /**
     * @param array $query
     * @return string
     */
    private function _findQuery(array $query = array())
    {
        $q = $this->_initQuery($query);

        return 'SELECT ' . $q['select'] . ' FROM ' . $this->getTable(true) . $q['join'] . $q['where'] . $q['groupby'] . $q['orderby'] . $q['limit'] . $q['offset'] . ';';
    }

    /**
     * @param array $query
     * @return array
     */
    private function _initQuery(array $query)
    {
        foreach ($query as $method => $val)
            \method_exists($this, $method) and $this->$method($val);

        return $this->qc;
    }

    /**
     * @return \Parith\Model\Model
     */
    private function clear()
    {
        $this->qc = self::$sql_clauses;
        $this->qp = array();

        return $this;
    }

    /**
     * @param string $select
     * @return \Parith\Model\Model
     */
    public function select($select = '*')
    {
        $this->qc['select'] = $select;

        return $this;
    }

    /**
     * @param mixed $join
     * @return \Parith\Model\Model
     */
    public function join($join)
    {
        foreach ((array )$join as $val)
            $this->qc['join'] .= stripos($val, 'JOIN') ? ' ' . $val : ' LEFT OUTER JOIN ' . $val; // false !==

        return $this;
    }

    /**
     * @param mixed $where
     * @param mixed $value
     * @return \Parith\Model\Model
     */
    public function where($where, $value = null)
    {
        if (\is_array($where)) {
            foreach ($where as $key => $val)
                $this->_jointWhere($key, $val);
        } else
            $this->_jointWhere($where, $value);

        return $this;
    }

    /**
     * @param mixed $where
     * @param mixed $value
     * @return \Parith\Model\Model
     */
    private function _jointWhere($where, $value)
    {
        if ($value !== null) {
            $qkey = ':qpw_' . $where;

            $where = '`' . $where . '`=' . $qkey;

            $this->qp[$qkey] = $value;
        }

        $this->qc['where'] .= ($this->qc['where'] ? ' AND ' : ' WHERE ') . $where;

        return $this;
    }

    /**
     * @param string $by
     * @return \Parith\Model\Model
     */
    public function groupby($by)
    {
        $this->qc['groupby'] = ' GROUP BY ' . $by;

        return $this;
    }

    /**
     * @param mixed $by
     * @return \Parith\Model\Model
     */
    public function orderby($by)
    {
        if ($by === 'ASC' || $by === 'DESC')
            $by = '`' . $this->primary . '` ' . $by;

        $this->qc['orderby'] = ' ORDER BY ' . $by;

        return $this;
    }

    /**
     * @param mixed $limit
     * @return \Parith\Model\Model
     */
    public function limit($limit)
    {
        $this->qc['limit'] = ' LIMIT ' . $limit;

        return $this;
    }

    /**
     * @param mixed $offset
     * @return \Parith\Model\Model
     */
    public function offset($offset)
    {
        $this->qc['offset'] = ' OFFSET ' . $offset;

        return $this;
    }

    /**
     * @param bool $alias
     * @return string
     */
    public function getTable($alias = true)
    {
        $table = '`' . $this->table . '`';

        return $alias ? $table . ' ' . $this->alias : $table;
    }

    /**
     * @param int $method
     * @return \Parith\Model\Model
     */
    public function setInsertMethod($method)
    {
        $this->_dml_insert = $method;
    }

    /**
     * @return array
     */
    public function getRelation()
    {
        return $this->_rels;
    }

    /**
     * @return \Parith\Model\Model
     */
    private function _initRelation()
    {
        static $default = array('reference' => null, 'cascade' => 0);

        foreach ((array )$this->relations() as $name => $cfg)
        {
            // relation type, relation class, foreign key
            if (isset($cfg[0], $cfg[1], $cfg[2]))
                $this->_rels[$name] = $cfg + $default;
            else
                throw new \Parith\Exception('Class ' . get_class($this) . 'has an invalid configuration for relation: ' . $name . '. It must specify the relation type, the relation class and the foreign key');
        }

        return $this;
    }
}

/**
 * ModelResult
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
class ModelResult extends \Parith\Result
{
    private $_base;

    /**
     * @param \Parith\Model\Model $model
     * @return \Parith\Model\ModelResult
     */
    public function __construct(\Parith\Model\Model $model)
    {
        $this->_base = $model;
    }

    /**
     * @return \Parith\Model\Model
     */
    public function baseModel()
    {
        return $this->_base;
    }

    /**
     * @param array $var
     * @return bool
     */
    public function save(array $var = array())
    {
        return $this->update($var);
    }

    /**
     * @param array $var
     * @return bool
     */
    public function update(array $var = array())
    {
        return $this->baseModel()->update($var + $this->resultGet());
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $model = $this->baseModel();
        return $model->delete($this->resultGet($model->primary));
    }

    /**
     * @param mixed $ref
     * @return mixed
     */
    public function findRelation($ref = null)
    {
        $model = $this->baseModel();
        return $model->findRelation($this->resultGet($model->primary), $ref);
    }
}