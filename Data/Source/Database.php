<?php

/**
 * Database, Based on PDO
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

namespace Parith\Data\Source;

class Database extends \Parith\Data\Source
{
    const
        MODIFIER_INSERT = 'INSERT INTO',
        MODIFIER_INSERT_IGNORE = 'INSERT IGNORE INTO',
        MODIFIER_REPLACE = 'REPLACE INTO';

    public $sth;

    public static $options = array(
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => null,
        'username' => 'root',
        'password' => null,
        'options' => array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            //\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,

            #overwrite 'options' if not using MySQL
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, //1000
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', //1002
            \PDO::MYSQL_ATTR_FOUND_ROWS => true, //1008
        )
    );

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

    public static function instanceKey($options)
    {
        return $options['host'] . ':' . $options['port'] . ':' . $options['dbname'];
    }

    /**
     * @param $table
     * @param $data
     * @param string $where
     * @return mixed
     */
    public function update($table, array $data, $where = '')
    {
        $params = array();

        $update = '';
        $glue = '';
        foreach ($data as $col => $val) {
            $update .= $glue . '`' . $col . '`= ?';

            $params[] = $val;

            $glue = ', ';
        }

        list($where, $params) = static::where($where, $params);

        $this->query('UPDATE ' . $table . ' SET ' . $update . $where . ';', $params);

        return $this->rowCount();
    }

    /**
     * @param $table
     * @param $data
     * @param string $modifier
     * @return mixed
     */
    public function insert($table, array $data, $modifier = null)
    {
        $params = array();

        $col = '';
        $value = '';
        $glue = '';
        foreach ($data as $k => $v) {
            $col .= $glue . '`' . $k . '`';
            $value .= $glue . '?';

            $params[] = $v;

            $glue = ', ';
        }

        $modifier or $modifier = self::MODIFIER_INSERT;

        if ($this->query($modifier . ' ' . $table . ' (' . $col . ') VALUES (' . $value . ');', $params))
            return $this->rowCount();

        return false;
    }

    public function delete($table, $where = '')
    {
        list($where, $params) = static::where($where);

        $this->query('DELETE FROM ' . $table . $where . ';', $params);

        return $this->rowCount();
    }

    /**
     * @static
     * @param $table
     * @param string $fields
     * @param string $where
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @param string $group
     * @param string $join
     * @return array
     */
    public static function selectParams($table, $fields = '*', $where = '', $limit = 0, $offset = 0, $order = '', $group = '', $join = '')
    {
        list($where, $params) = static::where($where);

        return array('SELECT ' . static::field($fields) . ' FROM ' . static::table($table) . static::join($join) .
            $where . static::groupBy($group) . static::orderBy($order) . static::limit($limit, $offset) . ';',
            $params
        );
    }

    /**
     * @static
     * @param $fields
     * @return mixed
     */
    public static function field($fields)
    {
        if ($fields)
            return $fields;

        return '*';
    }

    /**
     * @static
     * @param $table
     * @return mixed
     */
    public static function table($table)
    {
        return '`' . $table . '`';
    }

    /**
     * @static
     * @param string|array $where
     *              - `gender`='male' AND `age`>=18 OR `email` LIKE '%@qq.com'
     *              - array(
     *                      'gender' => 'male',
     *                      'email' => array('LIKE', '%@qq.com', 'OR')
     *                      'time' => array('>=', 0)
     *                      array('<=', 1325347200 , 'AND', 'time')
     *                  )
     *
     * @param array $params
     * @return array
     */
    public static function where($where, array $params = array())
    {
        $query = '';

        if (is_array($where)) {
            foreach ($where as $col => $val) {
                if (is_array($val)) {
                    $val += array('=', '', 'AND', '');

                    if ($val[3])
                        $col = $val[3];

                } else {
                    $val = array('=', $val, 'AND');
                }

                $query .= ' ' . $val[2] . ' `' . $col . '`' . $val[0] . ' ?'; // means: "AND `gender` = ?"

                $params[] = $val[1];
            }
        } elseif ($where) {
            $query = ' AND ' . $where;
        }

        // return an array with the SQL query + params
        return array(' WHERE 1' . $query, $params);
    }

    /**
     * @static
     * @param $limit
     * @param int $offset
     * @return string
     */
    public static function limit($limit, $offset = 0)
    {
        if ($limit)
            return ' LIMIT ' . $offset . ', ' . $limit;

        return '';
    }

    /**
     * @static
     * @param $group
     * @return string
     */
    public static function groupBy($group)
    {
        if ($group) {
            return ' GROUP BY ' . $group;
        }

        return '';
    }

    /**
     * @static
     * @param string|array $order
     *          - 'id'
     *          - array('id', 'ts' => -1)
     *
     * @return mixed
     */
    public static function orderBy($order)
    {
        if (!$order)
            return '';

        $ret = ' ORDER BY ';

        if (is_array($order)) {
            $glue = '';

            foreach ($order as $col => $expr) {
                if (\is_int($col)) {
                    $col = $expr;
                    $expr = 'ASC';
                } elseif (-1 == $expr) {
                    $expr = 'DESC';
                }

                $ret .= $glue . '`' . $col . '` ' . $expr;
                $glue = ', ';
            }
        } else {
            $ret .= $order;
        }

        return $ret;
    }

    /**
     * @static
     * @param array $join
     *          - array('comment' => 'blog.comment_id=comment.id')
     *          - array('comment' => array('on' => 'blog.comment_id=comment.id', 'type' => 'INNER JOIN'))
     *
     * @return string
     */
    public static function join(array $join)
    {
        $ret = '';
        foreach ($join as $table => $expr) {
            if (!\is_array($expr)) {
                $expr = array('on' => $expr, 'type' => 'INNER JOIN');
            }

            $ret .= ' ' . $expr['type'] . ' `' . $table . '` ON ' . $expr['on'];
        }

        return $ret;
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function query($query, array $params = array())
    {
        $this->sth = $this->link->prepare($query);

        return $this->sth->execute($params);
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
     * @param $query
     * @param array $params
     * @param int|array $mode
     * @return mixed
     */
    public function fetch($query, array $params = array(), $mode = 0)
    {
        $this->query($query, $params);

        if ($mode)
            $this->setFetchMode($mode);

        return $this->sth->fetch();
    }

    /**
     * @param $query
     * @param array $params
     * @param int|array $mode
     * @return mixed
     */
    public function fetchAll($query, array $params = array(), $mode = 0)
    {
        $this->query($query, $params);

        if ($mode)
            $this->setFetchMode($mode);

        return $this->sth->fetchAll();
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

    public function setAttribute($attr, $var)
    {
        $this->link->setAttribute($attr, $var);
        return $this;
    }

    public function close()
    {
        $this->link = null;
        return true;
    }
}