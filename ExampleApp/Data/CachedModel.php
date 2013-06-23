<?php

namespace Jieru\Data;

use \Jieru\Lib\Response;

abstract class CachedModel extends \Parith\Data\Model\Database
{
    public
        $cache = null,
        $cache_key = '',
        $cache_data = array(),

        $db_name = '',
        $db_options = array(),
        $table_name = '',

        $value_key = false;

    public function __construct()
    {
        parent::__construct();

        $this->cache = Cache::connection(array());

        $this->db_options = \Parith\App::getOption('database');
        $this->db_options['dbname'] = $this->db_name;
    }

    public function connection($options, $query = array())
    {
        return parent::connection($this->db_options);
    }

    public function source($source, $data)
    {
        return $this->table_name;
    }

    public function cacheKey($key)
    {
        if (is_array($key))
            $key = implode('_', $key);

        return $this->cache_key = $this->source('', '') . '|' . $key;
    }

    public function makeKey(array $data)
    {
        $ret = array();

        //if undefined $data[$k] will raise notice
        foreach ((array)$this->primary_key as $k)
            $ret[] = $data[$k];

        return $ret;
    }

    public function tableKey($data)
    {
        return implode('_', $this->makeKey($data));
    }

    public function get($key)
    {
        $ret = $this->cache->get($this->cacheKey($key));
        if ($ret)
            return $ret;

        if ($this->value_key) {
            $ret = $this->fetch(array('key' => $key, ':fields' => $this->value_key), null, array(\PDO::FETCH_COLUMN, 0));
            if ($ret)
                $ret = json_decode($ret, true);
        } else
            $ret = $this->fetch($key);

        if ($ret)
            $this->cache->set($this->cache_key, $ret);

        return $ret;
    }

    protected function prepareData($data)
    {
        if ($this->value_key)
            $data = array('key' => $this->tableKey($data), $this->value_key => json_encode($data));

        return $data;
    }

    public function add(array $data)
    {
        $db_data = $this->prepareData($data);

        $ret = parent::insert($db_data) or $this->ds->dumpParams();

        if ($ret)
            $this->cache->set($this->cacheKey($this->makeKey($data)), $data);

        return $ret;
    }

    public function set(array $data)
    {
        $db_data = $this->prepareData($data);

        $ret = parent::update($db_data) or $this->ds->dumpParams();

        if ($ret)
            $this->cache->set($this->cacheKey($this->makeKey($data)), $data);

        return $ret;
    }

    public function remove($key)
    {
        $key or $key = $this->keys();

        $ret = parent::delete($key) or $this->ds->dumpParams();

        //force delete cache
        $this->cache->delete($this->cacheKey($key));

        return $ret;
    }
}
