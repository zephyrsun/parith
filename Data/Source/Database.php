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
        DML_INSERT = 'INSERT INTO',
        DML_INSERT_IGNORE = 'INSERT IGNORE INTO',
        DML_REPLACE = 'REPLACE INTO';

    public $options = array(
        'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306, 'dbname' => null,
        'username' => 'root', 'password' => null, 'options' => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        )
    ), $stmt;

    /**
     * @param array $options
     * @return Database
     */
    public function connect($options = array())
    {
        $options = $this->option($options);

        try {
            $this->link = new \PDO(
                "{$options['driver']}:host={$options['host']};port={$options['port']};dbname={$options['dbname']}",
                $options['username'],
                $options['password'],
                $options['options']
            );

            $this->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }
        catch (\PDOException $e) {
            \Parith\Exception::handler($e);
        }

        return $this;
    }

    /**
     * @param $table
     * @param $data
     * @param string $where
     * @return mixed
     */
    public function update($table, $data, $where = '')
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

        return $this->query('UPDATE ' . $table . ' SET ' . $update . $where . ';', $params);
    }

    /**
     * @param $table
     * @param $data
     * @param string $operator
     * @return mixed
     */
    public function insert($table, $data, $operator = self::DML_INSERT)
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

        return
            $this->query($operator . ' ' . $table . ' (' . $col . ') VALUES (' . $value . ');', $params)
                ? $this->lastInsertId()
                : false;
    }

    public function delete($table, $where = '')
    {
        list($where, $params) = static::where($where);

        $this->query('DELETE FROM ' . $table . $where . ';', $params);

        return $this->rowCount();
    }

    /**
     * @param $table
     * @param string $fields
     * @param string $where
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @return array
     */
    public static function selectSql($table, $fields = '*', $where = '', $limit = 0, $offset = 0, $order = '')
    {
        list($where, $params) = static::where($where);

        return array('SELECT ' . static::field($fields) . ' FROM ' . static::table($table) .
            $where . static::order($order) . static::limit($limit, $offset) . ';',
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
        return $fields ? $fields : '*';
    }

    /**
     * @static
     * @param $table
     * @return mixed
     */
    public static function table($table)
    {
        return $table;
    }

    /**
     * @static
     * @param string|array $where
     *              - `gender`='male' AND `age`>=18 OR `email` LIKE '%@qq.com'
     *              - array('gender' => 'male', 'age' => array('>=', 18), 'email' => 'LIKE', '%@qq.com', 'OR')
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
                    $val += array('=', '', ' AND ');
                }
                else {
                    $val = array('=', $val, ' AND ');
                }

                $query .= $val[2] . '`' . $col . '`' . $val[0] . ' ?'; // means: "AND `gender` = ?"

                $params[] = $val[1];
            }
        }
        elseif ($where) {
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
     * @param string|array $order
     *          - 'id'
     *          - array('id', 'ts' => -1)
     *
     * @return mixed
     */
    public static function order($order)
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
                }
                elseif (-1 == $expr) {
                    $expr = 'DESC';
                }

                $ret .= $glue . '`' . $col . '` ' . $expr;
                $glue = ', ';
            }
        }
        else {
            $ret .= $order;
        }

        return $ret;
    }

    /**
     * @static
     * @param $join
     *          - 'blog'
     *          - array('blog', 'INNER JOIN' => 'comments')
     *
     * @return string
     */
    public static function join($join)
    {
        $ret = '';
        if (is_array($join)) {
            foreach ($join as $expr => $col) {
                if (\is_int($expr))
                    $expr = 'LEFT OUTER JOIN';

                $ret .= ' ' . $expr . ' `' . $col . '`';
            }
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
        $this->stmt = $this->link->prepare($query);

        return $this->stmt->execute($params);
    }

    /**
     * @param $query
     * @param array $params
     * @param int|object $mode
     * @return mixed
     */
    public function fetch($query, array $params = array(), $mode = 0)
    {
        $this->query($query, $params);

        return $this->_setFetchMode($mode)->fetch();
    }

    /**
     * @param $query
     * @param array $params
     * @param int|object $mode
     * @return mixed
     */
    public function fetchAll($query, array $params = array(), $mode = 0)
    {
        $this->query($query, $params);

        return $this->_setFetchMode($mode)->fetchAll();
    }

    private function _setFetchMode($mode)
    {
        if (\is_object($mode))
            $this->stmt->setFetchMode(\PDO::FETCH_INTO, $mode);
        elseif ($mode)
            $this->stmt->setFetchMode($mode);

        return $this->stmt;
    }

    /**
     * @return string
     */
    public function getLastSql()
    {
        return $this->stmt->queryString;
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
        return $this->stmt->errorInfo();
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function setCharset($charset)
    {
        $this->query('SET NAMES ' . $charset . ';');
        return $this;
    }

    /**
     * @param $attr
     * @param $var
     * @return Database
     */
    public function setAttribute($attr, $var)
    {
        $this->link->setAttribute($attr, $var);
        return $this;
    }

    /**
     * @return Database
     */
    public function close()
    {
        $this->link = null;
        return $this;
    }
}