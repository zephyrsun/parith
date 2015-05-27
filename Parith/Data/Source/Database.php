<?php

/**
 * Database, Based on PDO
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Data\Source;

use \Parith\Data\Source;

class Database extends Source
{
    const
        MODIFIER_INSERT = 'INSERT INTO',
        MODIFIER_INSERT_IGNORE = 'INSERT IGNORE INTO',
        MODIFIER_REPLACE = 'REPLACE INTO';

    /**
     * @var \PDOStatement
     */
    public $sth;

    /**
     * @var \PDO
     */
    public $link;

    public $table_name = '';
    public $clauses = array();
    public $sql = '';
    public $params = array();

    public $options = array(
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'db_name' => '',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'options' => array(),
    );

    public $server_options = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        //\PDO::ATTR_PERSISTENT => false,

        #overwrite 'options' if not using MySQL
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, //1000
        \PDO::MYSQL_ATTR_FOUND_ROWS => true, //1008
    );

    private $_info = array();

    private static $_pool = array();

    public function __construct(array $options = array())
    {
        $this->initial();
        $this->option($options);
    }

    /**
     * @return $this
     */
    public function connect()
    {
        $k = $this->options['host'] . ':' . $this->options['port'] . ':' . $this->options['db_name'];

        if (isset(self::$_pool[$k])) {
            $this->link = self::$_pool[$k];
        } else {
            $this->link = self::$_pool[$k] = $this->getLink();
        }

        return $this;
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    protected function getLink()
    {
        $options = $this->options;

        try {
            $this->link = new \PDO(
                "{$options['driver']}:host={$options['host']};port={$options['port']};dbname={$options['db_name']}",
                $options['username'],
                $options['password'],
                $this->server_options
            );
        } catch (\PDOException $e) {
            //$this->_setLastInfo($e->getMessage());
            throw new \Exception("Fail to connect: {$options['host']}:{$options['port']}:{$options['db_name']}");
        }

        return $this->link;
    }

    public function option(array $options)
    {
        $this->options = $options + $this->options;

        $this->server_options = $this->options['options'] + $this->server_options;

        if ($this->options['charset'])
            $this->server_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->options['charset'];

        return $this;
    }

    /**
     * @return Database
     */
    public function initial()
    {
        $this->clauses = array(
            'fields' => '*',
            'table' => $this->table_name,
            'join' => '',
            'where' => ' WHERE 1',
            'group' => '',
            'having' => '',
            'order' => '',
            'limit' => '',
        );

        $this->params = array();

        return $this;
    }

    /**
     * @param $fields
     *
     * @return Database
     */
    public function field($fields)
    {
        $this->clauses['fields'] = $fields;

        return $this;
    }

    /**
     * @param $table
     *
     * @return Database
     */
    public function table($table)
    {
        $this->clauses['table'] = $table;

        return $this;
    }

    /**
     * where('gender', 'male')
     * where('email', 'LIKE', '%@abc.com', 'OR')
     * handle as a full clause when has "?"
     * where('(age >= ? OR age <= ?)', array(18, 30))
     *
     * @param $clause
     * @param $condition
     * @param $value
     * @param $glue
     *
     * @return Database
     */
    public function where($clause, $condition, $value = null, $glue = 'AND')
    {
        if ($value === null) {

            $value = $condition;
            if (strpos($clause, '?') === false) {
                $condition = '= ?';
            } else {
                $condition = '';
            }

        } elseif ($condition == 'IN') {
            $in = substr(str_repeat(',?', count($value)), 1);
            $condition = "IN ($in)";

        } else {
            $condition .= ' ?';
        }

        $this->clauses['where'] .= " $glue $clause $condition";

        if (is_array($value)) {
            $this->params = array_merge($this->params, $value);
        } else {
            $this->params[] = $value;
        }

        return $this;
    }

    /**
     * @param     $limit
     * @param int $offset
     *
     * @return Database
     */
    public function limit($limit, $offset = 0)
    {
        if ($limit) {
            $this->clauses['limit'] = ' LIMIT ' . $offset . ', ' . $limit;
        }

        return $this;
    }

    /**
     * @param $group
     *
     * @return Database
     */
    public function groupBy($group)
    {
        if ($group) {
            $this->clauses['group'] = ' GROUP BY ' . $group;
        } else {
            $this->clauses['group'] = '';
        }

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
        if (!$order) {
            return $this;
        }

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
        } else {
            $clause .= $order;
        }

        $this->clauses['order'] = $clause;

        return $this;
    }

    /**
     * @param $where
     *
     * @return $this
     */
    public function having($where)
    {
        if ($where) {
            $this->clauses['having'] = ' HAVING  ' . $where;
        }

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
        if (!$join) {
            return $this;
        }

        $clause = '';
        foreach ($join as $table => $expr) {
            \is_array($expr) or $expr = array('on' => $expr, 'type' => 'INNER JOIN');

            $clause .= ' ' . $expr['type'] . ' ' . $table . ' ON ' . $expr['on'];
        }

        $this->clauses['join'] .= $clause;

        return $this;
    }

    /**
     * @param        $data
     * @param string $modifier
     *
     * @return int|bool
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

        $this->sql = $modifier . ' ' . $this->clauses['table'] . ' (' . \implode(', ', $col) . ') VALUES (' . \implode(', ', $value) . ');';

        $ret = $this->exec();

        if ($ret && $id = $this->lastInsertId())
            return $id;

        return $ret;
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
        //var_dump($this->clauses['table']);

        $this->sql = 'UPDATE ' . $this->clauses['table'] . ' SET ' . $data . $this->clauses['where'];
        //var_dump($this->sql);
        $ret = $this->exec();

        if ($ret && $n = $this->rowCount())
            return $n;

        //overwrite params
        $this->_info += array(
            'error' => array(),
            'sql' => $this->sql,
            'params' => $params,
        );

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
        $this->exec();

        return $this->rowCount();
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
        $this->exec();

        return $this->_setFetchMode($mode, $mode_param)->fetch();
    }

    public function fetchColumn($col = 0)
    {
        return $this->fetch(\PDO::FETCH_COLUMN, $col);
    }

    /**
     * @param int $mode
     * @param mixed $mode_param
     * @param bool $reset set as false，when need fetchAllCount()
     *                     $data = $this->fetchAll(0, null, false)
     *                     $count = $this->fetchAllCount()
     *
     * @return array
     */
    public function fetchAll($mode = 0, $mode_param = null, $reset = true)
    {
        $this->sql = $this->getSelectClause();
        $this->exec($reset);

        return $this->_setFetchMode($mode, $mode_param)->fetchAll();
    }

    public function fetchAllCount()
    {
        return $this->field('count(*)')->groupBy('')->limit(1)->fetchColumn(0);
    }

    /**
     * @param string $query
     * @param $params
     *
     * @return bool
     */
    public function query($query, $params = array())
    {
        $this->sql = $query;
        $this->params = $params;

        return $this->exec();
    }

    public function exec($reset = true)
    {
        $this->link or $this->connect();

        $this->sth = $this->link->prepare($this->sql);

        if ($this->sth) {
            $result = $this->sth->execute($this->params) or $this->_setLastInfo($this->sth->errorInfo());

            if ($reset)
                $this->initial();

            return $result;
        }

        $this->_setLastInfo($this->link->errorInfo());

        return false;
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        $this->link or $this->connect();

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
        $c = & $this->clauses;

        return 'SELECT ' . $c['fields'] . ' FROM ' . $c['table'] . $c['join'] . $c['where'] . $c['group'] . $c['having'] . $c['order'] . $c['limit'] . ';';
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
            if ($mode_param === null) {
                $this->sth->setFetchMode($mode);
            } else {
                $this->sth->setFetchMode($mode, $mode_param);
            }
        }

        return $this->sth;
    }

    /**
     * @param $name
     *
     * @return int
     */
    public function lastInsertId($name = null)
    {
        return $this->link->lastInsertId($name);
    }

    private function _setLastInfo($error)
    {
        $this->_info = array(
            'error' => $error,
            'sql' => $this->sql,
            'params' => $this->params,
        );

        return $this->_info;
    }

    /**
     * @return array
     */
    public function getLastInfo()
    {
        if ($this->_info)
            return $this->_info;

        return $this->_setLastInfo($this->link->errorInfo());
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
    public function getLastSql()
    {
        return $this->sql;
        //return $this->sth->queryString;
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

    public function close()
    {
        $this->link = null;

        return $this;
    }

    public static function closeAll()
    {
        foreach (self::$_pool as &$link)
            $link = null;

    }
}