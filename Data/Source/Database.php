<?php

/**
 * Database, Based on PDO
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Data\Source;

class Database extends \Parith\Data\Source
{
    const
        MODIFIER_INSERT = 'INSERT INTO',
        MODIFIER_INSERT_IGNORE = 'INSERT IGNORE INTO',
        MODIFIER_REPLACE = 'REPLACE INTO';

    public $sth, $clauses = array(), $params = array();

    public static $options = array(
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => null,
        'username' => 'root',
        'password' => null,
        'options' => array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,

            #overwrite 'options' if not using MySQL
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, //1000
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', //1002
            \PDO::MYSQL_ATTR_FOUND_ROWS => true, //1008
        )
    );

    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->initial();
    }

    /**
     * @param array $options
     * @return mixed|void
     */
    public function connect(array $options)
    {
        $options = static::option($options);

        try {
            $this->link = new \PDO(
                "{$options['driver']}:host={$options['host']};port={$options['port']};dbname={$options['dbname']}",
                $options['username'],
                $options['password'],
                $options['options']
            );
        } catch (\PDOException $e) {
            \Parith\Exception::handler($e);
        }
    }

    /**
     * @param $options
     * @return string
     */
    public static function instanceKey($options)
    {
        return $options['host'] . ':' . $options['port'] . ':' . $options['dbname'];
    }

    /**
     * @return Database
     */
    public function initial()
    {
        $this->clauses = array(
            'fields' => '*',
            'table' => '',
            'join' => '',
            'where' => ' WHERE 1',
            'group' => '',
            'order' => '',
            'limit' => '',
        );

        $this->params = array();

        return $this;
    }

    /**
     * @param $fields
     * @return Database
     */
    public function field($fields)
    {
        $this->clauses['fields'] = $fields;

        return $this;
    }

    /**
     * @param $table
     * @return Database
     */
    public function table($table)
    {
        $this->clauses['table'] = $table;

        return $this;
    }

    /**
     * where('gender', 'male')
     *
     * where('email', 'LIKE', '%@abc.com', 'OR')
     *
     * handle as a full clause when has "?"
     * where('(age >= ? OR age <= ?)', array(18, 30))
     *
     * @param $field
     * @param $operator
     * @param $value
     * @param $glue
     * @return Database
     */
    public function where($field, $operator, $value = '', $glue = 'AND')
    {
        if ($value) {
            $operator .= ' ?';
        } else {
            $value = $operator;
            if (strpos($field, '?') === false)
                $operator = '= ?';
            else
                $operator = '';
        }

        $this->clauses['where'] .= ' ' . $glue . ' ' . $field . $operator;

        if (is_array($value))
            $this->params = array_merge($this->params, $value);
        else
            $this->params[] = $value;

        return $this;
    }

    /**
     * @param $limit
     * @param int $offset
     * @return Database
     */
    public function limit($limit, $offset = 0)
    {
        if ($limit)
            $this->clauses['limit'] = ' LIMIT ' . $offset . ', ' . $limit;

        return $this;
    }

    /**
     * @param $group
     * @return Database
     */
    public function groupBy($group)
    {
        if ($group)
            $this->clauses['group'] = ' GROUP BY ' . $group;

        return $this;
    }

    /**
     * @param string|array $order
     *          - 'id'
     *          - array('id', 'ts' => -1)
     *
     * @return Database
     */
    public function orderBy($order)
    {
        if (!$order)
            return $this;

        $clause = ' ORDER BY ';

        if (is_array($order)) {
            $glue = '';

            foreach ($order as $col => $expr) {
                if (\is_int($col)) {
                    $col = $expr;
                    $expr = 'ASC';
                } elseif (-1 == $expr) {
                    $expr = 'DESC';
                }

                $clause .= $glue . '`' . $col . '` ' . $expr;
                $glue = ', ';
            }
        } else {
            $clause .= $order;
        }

        $this->clauses['order'] = $clause;

        return $this;
    }

    /**
     * @param array $join
     *          - array('comment' => 'blog.comment_id=comment.id')
     *          - array('comment' => array('on' => 'blog.comment_id=comment.id', 'type' => 'INNER JOIN'))
     *
     * @return string
     */
    public function join(array $join)
    {
        if (!$join)
            return $this;

        $clause = '';
        foreach ($join as $table => $expr) {
            \is_array($expr) or $expr = array('on' => $expr, 'type' => 'INNER JOIN');

            $clause .= ' ' . $expr['type'] . ' `' . $table . '` ON ' . $expr['on'];
        }

        $this->clauses['join'] = $clause;

        return $this;
    }

    /**
     * @param $data
     * @return int
     */
    public function update(array $data)
    {
        $value = $params = array();
        foreach ($data as $col => $val) {
            $value[] = '`' . $col . '`= ?';
            $params[] = $val;
        }

        // adjust order
        $this->params = array_merge($params, $this->params);

        $this->query('UPDATE ' . $this->clauses['table'] . ' SET ' . \implode(', ', $value) . $this->clauses['where'] . ';');

        return $this->rowCount();
    }

    /**
     * @param $data
     * @param string $modifier
     * @return mixed
     */
    public function insert(array $data, $modifier = null)
    {
        $col = $value = array();
        foreach ($data as $k => $v) {
            $col[] = '`' . $k . '`';

            $value[] = '?';

            $this->params[] = $v;
        }

        $modifier or $modifier = self::MODIFIER_INSERT;

        $ret = $this->query($modifier . ' ' . $this->clauses['table'] . ' (' . \implode(', ', $col) . ') VALUES (' . \implode(', ', $value) . ');');

        if ($ret) {
            $id = $this->lastInsertId();
            if ($id)
                return $id;
        }

        return $ret;
    }

    /**
     * @return int
     */
    public function delete()
    {
        $this->query('DELETE FROM ' . $this->clauses['table'] . $this->clauses['where'] . ';');
        return $this->rowCount();
    }

    /**
     * @param int $mode
     * @return mixed
     */
    public function fetch($mode = 0)
    {
        $this->query($this->_selectClause());

        if ($mode)
            $this->setFetchMode($mode);

        return $this->sth->fetch();
    }

    /**
     * @param int $mode
     * @return mixed
     */
    public function fetchAll($mode = 0)
    {
        $this->query($this->_selectClause());

        if ($mode)
            $this->setFetchMode($mode);

        return $this->sth->fetchAll();
    }

    private function _selectClause()
    {
        $c = $this->clauses;

        return 'SELECT ' . $c['fields'] . ' FROM ' . $c['table'] . $c['join'] . $c['where'] . $c['group'] . $c['order'] . $c['limit'] . ';';
    }

    /**
     * @param $query
     * @return mixed
     */
    public function query($query)
    {
        $this->sth = $this->link->prepare($query);

        $result = $this->sth->execute($this->params);

        $this->initial();

        return $result;
    }

    /**
     * @param int|array $mode
     * @return Database
     */
    protected function setFetchMode($mode)
    {
        if (is_array($mode))
            call_user_func_array(array($this->sth, 'setFetchMode'), $mode);
        else
            $this->sth->setFetchMode($mode);

        return $this;
    }

    /**
     * @return mixed
     */
    public function dumpParams()
    {
        return $this->sth->debugDumpParams();
    }

    /**
     * @return string
     */
    public function getLastSql()
    {
        return $this->sth->queryString;
    }

    /**
     * @param $name
     * @return int
     */
    public function lastInsertId($name = null)
    {
        return $this->link->lastInsertId($name);
    }

    /**
     * @return array
     */
    public function errorInfo()
    {
        return $this->sth->errorInfo();
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return $this->sth->rowCount();
    }

    public function setCharset($charset)
    {
        $this->query('SET NAMES ' . $charset . ';');

        return $this;
    }

    public function setAttribute($attr, $val)
    {
        return $this->link->setAttribute($attr, $val);
    }

    public function close()
    {
        $this->link = null;
        return $this;
    }

    public function __destruct()
    {
        $this->close();
    }
}