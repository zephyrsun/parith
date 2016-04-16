<?php

/**
 * Database, Based on PDO
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\DataSource;

use \Parith\App;

class Database extends Basic
{
    static protected $ins_n = 0;
    static protected $ins_link = array();

    /**
     * @var \PDOStatement
     */
    public $sth;

    /**
     * @var \PDO
     */
    public $link = null;

    public $table_name = '';

    public $sql = '';
    public $clauses = array();
    public $params = array();
    public $last_clauses = array();
    public $last_params = array();

    public $error = array();

    public $options = array(
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => '',
        'username' => 'root',
        'password' => '',
        'options' => array(),
    );

    public $server_options = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        //\PDO::ATTR_PERSISTENT => false,

        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',

        #overwrite 'options' if not using MySQL
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, //1000
        \PDO::MYSQL_ATTR_FOUND_ROWS => true, //1008
    );

    /**
     * @param $options
     * @return $this
     * @throws \Exception
     */
    public function dial($options)
    {
        $this->initial();

        if (!is_array($options))
            $options = App::getOption($options);

        $options += $this->options;

        $dsn = "{$options['driver']}:host={$options['host']};port={$options['port']};dbname={$options['dbname']}";

        self::$ins_n++;

        if ($link = &self::$ins_link[$dsn])
            return $this->link = $link;

        return $this->link = $link = new \PDO(
            $dsn,
            $options['username'],
            $options['password'],
            $options['options'] + $this->server_options
        );
    }

    /**
     * @return $this
     */
    public function initial()
    {
        $this->last_params = $this->params;
        $this->last_clauses = $this->clauses;

        $this->clauses = array(
            'fields' => '*',
            'table' => $this->table_name,
            'join' => '',
            'where' => ' WHERE 1',
            'group' => '',
            'having' => '',
            'order' => '',
            'limit' => '',
            'for_update' => '',
        );

        $this->params = array();

        return $this;
    }

    /**
     * fields for select
     *
     * @param $fields
     *        - *
     *        - id,uid,ts
     *        - count(*)
     * @return $this
     */
    public function field($fields)
    {
        $this->clauses['fields'] = $fields;

        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->clauses['table'] = $table;

        return $this;
    }

    /**
     * where('gender', 'male')
     * where('user_id', 'IN', array(1, 2, 3))
     * where('email', 'LIKE', '%@abc.com', 'OR')
     * where('(age >= ? OR age <= ?)', array(18, 30))
     *
     * @param $clause
     * @param $condition
     * @param $value
     * @param $glue
     *
     * @return $this
     */
    public function where($clause, $condition, $value = null, $glue = 'AND')
    {
        if ($value === null) {
            $value = $condition;

            if (strpos($clause, '?') === false) {
                $clause = "`$clause`";
                $condition = '= ?';
            } else
                $condition = '';

        } elseif ($condition == 'IN') {
            $in = '?' . str_repeat(',?', count($value) - 1);
            $condition = "IN ($in)";
        } else
            $condition .= ' ?';


        $this->clauses['where'] .= " $glue $clause $condition";

        if (is_array($value)) {
            $this->params = array_merge($this->params, $value);
        } else {
            $this->params[] = $value;
        }

        return $this;
    }

    /**
     * @param $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit, $offset = 0)
    {
        if ($limit)
            $this->clauses['limit'] = ' LIMIT ' . $offset . ', ' . $limit;

        return $this;
    }

    /**
     * @param string $group
     * @return $this
     */
    public function groupBy($group)
    {
        if ($group)
            $this->clauses['group'] = ' GROUP BY ' . $group;
        else
            $this->clauses['group'] = '';

        return $this;
    }

    /**
     * @param string|array $order
     *          - 'id'
     *          - array('id', 'ts' => -1)
     *
     * @return $this
     */
    public function orderBy($order)
    {
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

                $clause .= $glue . $col . ' ' . $expr;
                $glue = ', ';
            }
        } else
            $clause .= $order;

        $this->clauses['order'] = $clause;

        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function having($where)
    {
        if ($where)
            $this->clauses['having'] = ' HAVING  ' . $where;

        return $this;
    }

    /**
     * @param array $join
     *          - array('comment' => 'blog.comment_id=comment.id')
     *          - array('comment' => array('on' => 'blog.comment_id=comment.id', 'type' => 'INNER JOIN'))
     *
     * @return $this
     */
    public function join(array $join)
    {
        if (!$join)
            return $this;

        $clause = '';
        foreach ($join as $table => $expr) {
            if (!is_array($expr))
                $expr = array('on' => $expr, 'type' => 'INNER JOIN');

            $clause .= ' ' . $expr['type'] . ' ' . $table . ' ON ' . $expr['on'];
        }

        $this->clauses['join'] .= $clause;

        return $this;
    }

    /**
     * @param array $data
     * @param string $modifier
     *                 - INSERT INTO
     *                 - INSERT IGNORE INTO
     *                 - REPLACE INTO
     *
     * @return int
     */
    public function insert(array $data, $modifier = 'INSERT INTO')
    {
        $col = $value = array();
        foreach ($data as $k => $v) {
            $col[] = '`' . $k . '`';

            $value[] = '?';

            $this->params[] = $v;
        }

        $this->sql = $modifier . ' ' . $this->clauses['table'] . ' (' . \implode(', ', $col) . ') VALUES (' . \implode(', ', $value) . ');';

        if ($this->exec() && $id = $this->getInsertId())
            return $id;

        return 0;
    }

    /**
     * @param array $data
     * @return int
     */
    public function replace(array $data)
    {
        return $this->insert($data, 'REPLACE INTO');
    }

    /**
     * @param array|string $data
     *
     * @return int
     */
    public function update($data)
    {
        $params = array();
        if (is_array($data)) {
            $value = array();
            foreach ($data as $col => $val) {
                $value[] = "`{$col}` = ?";
                $params[] = $val;
            }

            // adjust order
            $this->params = $params = array_merge($params, $this->params);

            $data = \implode(', ', $value);
        }

        $this->sql = 'UPDATE ' . $this->clauses['table'] . ' SET ' . $data . $this->clauses['where'];

        if ($this->exec() && $n = $this->rowCount())
            return $n;

        return false;
    }

    /**
     * increase a field
     *
     * @param string $field
     * @param int $num
     *
     * @return int
     */
    public function increment($field, $num)
    {
        return $this->update("`{$field}`=`{$field}`+{$num}");
    }

    /**
     * @return int
     */
    public function delete()
    {
        $this->sql = 'DELETE FROM ' . $this->clauses['table'] . $this->clauses['where'] . ';';

        if ($this->exec() && $n = $this->rowCount())
            return $n;

        return false;
    }

    /**
     * @param int $mode
     * @param mixed $mode_param
     *
     * @return mixed
     */
    public function fetch($mode = 0, $mode_param = null)
    {
        $this->sql = $this->getSelectClause();

        if ($this->exec())
            return $this->_setFetchMode($mode, $mode_param)->fetch();

        return false;
    }

    public function fetchColumn($col = 0)
    {
        return $this->fetch(\PDO::FETCH_COLUMN, $col);
    }

    /**
     * @param int $mode
     * @param mixed $mode_param
     *
     * @return array
     */
    public function fetchAll($mode = 0, $mode_param = null)
    {
        $this->sql = $this->getSelectClause();

        if ($this->exec())
            return $this->_setFetchMode($mode, $mode_param)->fetchAll();

        return false;
    }

    /**
     * @param bool $clear_group_by
     * @return mixed
     */
    public function fetchCount($clear_group_by = true)
    {
        $this->clauses = $this->last_clauses;
        $this->params = $this->last_params;

        if ($clear_group_by)
            $this->groupBy('');

        return $this->field('count(*)')->limit(1)->fetchColumn(0);
    }

    /**
     * execute multi sql
     * @param $sql
     * @return \PDOStatement
     */
    public function multiQuery($sql)
    {
        $this->link->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $this->link->query($sql);
    }

    /**
     * @param string $sql
     * @param $params
     *
     * @return bool
     */
    public function query($sql, $params = array())
    {
        $this->sql = $sql;
        $this->params = $params;

        return $this->exec();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function exec()
    {
        $this->sth = $this->link->prepare($this->sql);
        if ($this->sth) {
            if ($result = $this->sth->execute($this->params)) {
                $this->initial();
                return $result;
            }

            $this->setError('PDOStatement:' . $this->sth->errorInfo()[2]);

        } else {
            $this->setError('PDO:' . $this->link->errorInfo()[2]);
        }

        return false;
    }

    public function setError($err)
    {
        $this->error = array(
            'error' => $err,
            'sql' => $this->sql,
            'params' => $this->params,
        );

        $this->initial();
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->link->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->link->commit();
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        return $this->link->rollBack();
    }

    /**
     * @param $params
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->params += $params;

        return $this;
    }

    public function getWhere()
    {
        return $this->clauses['where'];
    }

    /**
     * @return string
     */
    public function getSelectClause()
    {
        $c = $this->clauses;

        return 'SELECT ' . $c['fields'] . ' FROM ' . $c['table'] . $c['join'] . $c['where'] .
        $c['group'] . $c['having'] . $c['order'] .
        $c['limit'] . $c['for_update'] . ';';
    }

    public function selectForUpdate($nowait = false)
    {
        $for_update = ' FOR UPDATE';
        if ($nowait)
            $for_update .= ' NOWAIT';

        $this->clauses['for_update'] = $for_update;

        return $this;
    }

    /**
     * @param int $mode
     * @param mixed $mode_param
     *
     * @return \PDOStatement
     */
    private function _setFetchMode($mode, $mode_param)
    {
        if ($mode) {
            $mode_param === null
                ? $this->sth->setFetchMode($mode)
                : $this->sth->setFetchMode($mode, $mode_param);
        }

        return $this->sth;
    }

    /**
     * @param $name
     *
     * @return int
     */
    public function getInsertId($name = null)
    {
        return $this->link->lastInsertId($name);
    }

    /**
     * @return mixed
     */
    public function dumpParams()
    {
        $this->sth->debugDumpParams();
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return array($this->sql, $this->last_params);
        //return $this->sth->queryString;
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return $this->sth->rowCount();
    }

    public function closeAll()
    {
        /**
         * @var $link \PDO
         */
        foreach (static::$ins_link as &$link)
            $link = null;
    }
}