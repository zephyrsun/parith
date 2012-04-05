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

namespace Parith\DataSource;

class Database extends DataSource
{
    public $link, $defaults = array(
        'username' => 'root', 'password' => null,
        'driver' => 'mysql', 'host' => null, 'port' => 3306, 'dbname' => null,
        'charset' => 'utf8', 'options' => null,
    );

    private $_stmt, $_fetch_mode = array(\PDO::FETCH_ASSOC);

    /**
     * @return Database
     */
    public function __construct()
    {
        $this->config('Database');
    }

    /**
     * @param $id
     * @param array $options
     * @return Database
     */
    public function connect($id, array $options = array())
    {
        $options = $this->option($id, $options);

        try
        {
            $dsn = "{$options['driver']}:host={$options['host']};port={$options['port']};dbname={$options['dbname']}";

            $this->link = new \PDO($dsn, $options['username'], $options['password'], $options['options']);

            $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION)->setCharset($options['charset']);
        }
        catch (\PDOException $e)
        {
            \Parith\Exception::handler($e);
        }

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
     * @param $charset
     * @return Database
     */
    public function setCharset($charset)
    {
        $this->query('SET NAMES ' . $charset . ';');
        return $this;
    }

    /**
     * @param $sql
     * @param array $params
     * @return bool
     */
    public function query($sql, array $params = array())
    {
        try
        {
            $this->_stmt = $this->link->prepare($sql);
            return $this->_stmt->execute($params);
        }
        catch (\PDOException $e)
        {
            \Parith\Exception::handler($e);
        }

        return false;
    }

    /**
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function fetch($sql, array $params = array())
    {
        $this->query($sql, $params);
        return $this->getStatement()->fetch();
    }

    /**
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function fetchAll($sql, array $params = array())
    {
        $this->query($sql, $params);
        return $this->getStatement()->fetchAll();
    }

    /**
     * @return string
     */
    public function getLastSql()
    {
        return $this->_stmt->queryString;
    }

    /**
     * @param null $name
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
        return $this->_stmt->errorInfo();
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return $this->_stmt->rowCount();
    }

    /**
     * @param $mode
     * @param int $params
     * @return Database
     */
    public function setFetchMode($mode, $params = 0)
    {
        if (\is_object($mode)) {
            $this->_fetch_mode = array(\PDO::FETCH_INTO, $mode);
            return $this;
        }

        switch (\strtoupper($mode))
        {
            case 'ASSOC':
                $this->_fetch_mode = array(\PDO::FETCH_ASSOC);
                break;
            case 'NUM':
                $this->_fetch_mode = array(\PDO::FETCH_NUM);
                break;
            case 'OBJ':
                $this->_fetch_mode = array(\PDO::FETCH_OBJ);
                break;
            case 'BOTH':
                $this->_fetch_mode = array(\PDO::FETCH_BOTH);
                break;
            case 'NAMED':
                $this->_fetch_mode = array(\PDO::FETCH_NAMED);
                break;
            case 'LAZY':
                $this->_fetch_mode = array(\PDO::FETCH_LAZY);
                break;
            case 'PROPS_LATE':
                $this->_fetch_mode = array(\PDO::FETCH_PROPS_LATE);
                break;
            case 'COLUMN':
                $this->_fetch_mode = array(\PDO::FETCH_COLUMN, $params);
                break;
            default:
                $this->_fetch_mode = array(\PDO::FETCH_CLASS, $mode);
        }

        return $this;
    }

    /**
     * @return PDOStatement
     */
    public function getStatement()
    {
        /*
        $fm = $this->_fetch_mode;
        switch (count($fm))
        {
            case 1:
                $this->_stmt->setFetchMode($fm[0]);
                break;

            case 2:
                $this->_stmt->setFetchMode($fm[0], $fm[1]);
                break;
        }
        */

        \call_user_func_array(array($this->_stmt, 'setFetchMode'), $this->_fetch_mode);

        return $this->_stmt;
    }
}