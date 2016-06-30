<?php

namespace Example\Data;

class Database extends \Parith\DataSource\PDO
{
    public $cfg_key = 'database_1';
    public $primary = 'id';

    const PAGE = 30;

    public function __construct()
    {
        $this->dial($this->cfg_key);
    }

    public function getList($page)
    {
        $page = $page > 0 ? self::PAGE * ($page - 1) : 0;
        $this->limit(self::PAGE, $page)->orderBy("$this->primary DESC");

        $list = $this->fetchAll();

        return array(
            'list' => $list,
            'count' => $this->fetchCount(),
        );
    }

    public function getByID($id)
    {
        return $this->where($this->primary, $id)->fetch();
    }
} 