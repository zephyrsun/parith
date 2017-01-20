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

use Parith\View\Helper\Paginator;

class PDO extends DataSource
{
    static protected $ins_n = 0;
    static protected $ins_link = [];

    /**
     * @var \PDOStatement
     */
    public $sth;

    /**
     * @var \PDO
     */
    public $link;

    public $table_name = '';
    public $table_alias = '';
    public $pk = 'id';
    public $select = '*';

    public $sql = '';
    public $clauses = [];
    public $params = [];
    public $last_clauses = [];
    public $last_params = [];

    public $options = [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => '',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'options' => [],
    ];

    public $server_options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        //\PDO::ATTR_AUTOCOMMIT => false,
        //\PDO::ATTR_PERSISTENT => false,

        //\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',

        #overwrite 'options' if not using MySQL
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, //1000
        \PDO::MYSQL_ATTR_FOUND_ROWS => true, //1008
    ];

    public function __construct()
    {
        $this->initial();
    }

    /**
     * @param $options
     * @return $this
     */
    public function dial($options)
    {
        is_array($options) or $options = \Parith::getEnv($options);

        $o = $options + $this->options;

        $dsn = "{$o['driver']}:host={$o['host']};port={$o['port']};dbname={$o['dbname']};charset={$o['charset']}";

        self::$ins_n++;

        if ($link = &self::$ins_link[$dsn]) {
            $this->link = $link;
        } else {
            $this->link = $link = new \PDO(
                $dsn,
                $o['username'],
                $o['password'],
                $o['options'] + $this->server_options
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function initial()
    {
        $this->last_params = $this->params;
        $this->last_clauses = $this->clauses;

        $this->clauses = [
            'join' => '',
            'where' => '',
            'group' => '',
            'having' => '',
            'order' => '',
            'limit' => '',
        ];

        $this->params = [];

        return $this;
    }

    /**
     * fields for select
     *
     * @param $select
     *        - *
     *        - id,uid,ts
     *        - count(*)
     * @return $this
     */
    public function select($select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table_name = $table;

        return $this;
    }

    /**
     * @param $alias
     * @return $this
     */
    public function alias($alias)
    {
        $this->table_alias = $alias;

        return $this;
    }

    /**
     * where('gender', 'male')
     * where('user_id', 'IN', [1, 2, 3])
     * where('email', 'LIKE', '%@abc.com', 'OR')
     * where('(age >= ? OR age <= ?)', [18, 30])
     * where("(nickname LIKE ? OR nickname = ?)", ['%|sun|%', 'sun'])
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

        } elseif ($condition == 'IN' || $condition == 'NOT IN') {

            is_array($value) or $value = explode(',', $value);
            $in = '?' . str_repeat(',?', count($value) - 1);
            $condition = "$condition ($in)";
        }

        $where = &$this->clauses['where'] or $where = ' WHERE 1';

        $where .= " $glue $clause $condition";

        if (is_array($value)) {
            $this->params = array_merge($this->params, $value);
        } elseif ($value !== null) {
            $this->params[] = $value;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->clauses['where'];
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
     *          - ['id', 'ts' => -1]
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
     * join('table2', ['id' => 'ref_id'], 'STRAIGHT_JOIN')
     * join('table2 t2', 't1.id=t2.ref_id', 'STRAIGHT_JOIN')
     *
     * @param $table
     * @param $condition
     * @param string $type
     * @return $this
     */
    public function join($table, $condition, $type = 'LEFT JOIN')
    {
        if (is_array($condition)) {
            $t2 = explode(' ', $table);
            $t2 = end($t2);

            $t1 = $this->table_alias or $t1 = $this->table_name;

            $k = key($condition);
            $ref_k = current($condition);

            $condition = "$t1.$k=$t2.$ref_k";
        }

        $this->clauses['join'] .= " $type ($table) ON ($condition)";

        return $this;
    }

    /**
     * @param array $data
     * @param string $op
     *                 - INSERT INTO
     *                 - INSERT IGNORE INTO
     *                 - REPLACE INTO
     *
     * @return int
     */
    public function insert(array $data, $op = 'INSERT INTO')
    {
        $col = $value = [];
        foreach ($data as $k => $v) {
            $col[] = '`' . $k . '`';

            $value[] = '?';

            $this->params[] = $v;
        }

        $this->sql = $op . ' ' . $this->table_name . ' (' . \implode(', ', $col) . ') VALUES (' . \implode(', ', $value) . ');';

        $this->exec();
        if ($id = $this->link->lastInsertId())
            return $id;

        return $this->sth->rowCount();
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
        if (is_array($data)) {
            $data = \implode(', ', $this->convert($data));
        }

        $this->sql = 'UPDATE ' . $this->table_name . ' SET ' . $data . $this->clauses['where'];

        return $this->exec()->rowCount();
    }

    public function save($data)
    {
        if (isset($data[$this->pk])) {
            return $this->where($this->pk, $data[$this->pk])->update($data);
        }

        return $this->insert($data);
    }

    protected function convert($data)
    {
        $value = [];
        $params = [];
        foreach ($data as $col => $val) {
            $value[] = "{$col} = ?";
            $params[] = $val;
        }

        // adjust order
        $this->params = array_merge($params, $this->params);

        return $value;
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
        $this->sql = 'DELETE FROM ' . $this->table_name . $this->clauses['where'] . ';';

        return $this->exec()->rowCount();
    }

    /**
     * shortcut for $this->fetch()
     *
     * $this->find(1)
     * $this->find(['user_id' => 1])
     *
     * @param $value
     * @return mixed
     */
    public function find($value)
    {
        if (is_array($value)) {
            $value = \implode(' AND ', $this->convert($value));
            $this->where($value, null);
        } else {
            $this->where($this->pk, $value);
        }

        return $this->fetch();
    }

    /**
     * shortcut for $this->fetchAll()
     *
     * $this->findAll(1)
     * $this->findAll(['user_id' => 1])
     *
     * @param $value
     * @return mixed
     */
    public function findAll($value)
    {
        if (is_array($value)) {
            $value = \implode(' AND ', $this->convert($value));
            $this->where($value, null);
        } else {
            $this->where($this->pk, $value);
        }

        return $this->fetchAll();
    }

    /**
     * @return mixed
     */
    public function fetch()
    {
        $this->getSelectClause();
        return $this->exec()->fetch();
    }

    /**
     * $this->fetchColumn(0)
     * $this->fetchColumn('id')
     * $this->fetchColumn('GROUP_CONCAT(DISTINCT id)')
     * $this->fetchColumn('SUM(num)')
     *
     * @param int|string $col
     * @return mixed
     */
    public function fetchColumn($col)
    {
        if (!is_numeric($col)) {
            $this->select($col);
            $col = 0;
        }

        return $this->setFetchMode(\PDO::FETCH_COLUMN, $col)->fetch();
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        $this->getSelectClause();
        return $this->exec()->fetchAll();
    }

    /**
     * @param $size
     * @param string $col
     * @return Paginator
     */
    public function paginate($size, $col = '')
    {
        if ($col) {
            //use start id
            $id = &$_GET[$col];
            $this->where('col > ?', $id);
            $this->limit($size);
        } else {
            $page = &$_GET['page'];
            $this->limit($size, $page > 0 ? $size * ($page - 1) : 0);
        }

        $list = $this->fetchAll();

        $this->clauses = $this->last_clauses;
        $this->params = $this->last_params;

        $count = $this->fetchColumn('COUNT(*)');

        return (new Paginator($count, $size))->merge($list);
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
     * @param $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, $params = [])
    {
        $this->sql = $sql;
        $this->params = $params;

        return $this->exec();
    }

    /**
     * @return \PDOStatement
     * @throws \Error
     * @throws \Exception
     */
    public function exec()
    {
        //prevent Segmentation fault in some PHP version
        //if ($this->sth)
        //    $this->sth->closeCursor();

        try {
            $this->sth = $this->link->prepare($this->sql);

            $this->sth->execute($this->params);

            $this->initial();

            return $this->sth;

        } catch (\PDOException $e) {
            $str = $e->getMessage() . PHP_EOL .
                'SQL: ' . $this->sql . PHP_EOL .
                'Params: "' . implode('","', $this->params) . '"' . PHP_EOL;

            throw new \Exception($str);
        } catch (\Error $e) {
            throw $e;
        }
    }

    /**
     * @param callable $cb
     * @return bool
     * @throws \Exception
     */
    public function transaction(callable $cb)
    {
        $r = $this->link->beginTransaction();
        if ($r) {
            try {
                $cb();

                return $this->link->commit();

            } catch (\Exception $e) {
                $this->link->rollBack();

                throw $e;
            }
        }

        return false;
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

    /**
     * @return string
     */
    public function getSelectClause()
    {
        $c = $this->clauses;

        return $this->sql = 'SELECT ' . $this->select . ' FROM ' . $this->table_name . ' ' . $this->table_alias .
            $c['join'] . $c['where'] .
            $c['group'] . $c['having'] . $c['order'] .
            $c['limit'] . ';';
    }

    /**
     * @param bool $nowait
     * @return $this
     */
//    public function selectForUpdate($nowait = false)
//    {
//        $for_update = ' FOR UPDATE';
//        if ($nowait)
//            $for_update .= ' NOWAIT';
//
//        $this->clauses['for_update'] = $for_update;
//
//        return $this;
//    }

    /**
     * @param $mode
     * @param $mode_param
     * @return \PDOStatement
     */
    public function setFetchMode($mode, $mode_param)
    {
        $this->getSelectClause();

        $this->exec();

        $mode_param === null
            ? $this->sth->setFetchMode($mode)
            : $this->sth->setFetchMode($mode, $mode_param);

        return $this->sth;
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