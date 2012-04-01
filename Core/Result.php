<?php

/**
 * Result
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

namespace Parith;

abstract class Result implements \Iterator, \ArrayAccess, \Countable
{
    protected $_rs = array();

    /**
     * @param $key
     * @param $var
     * @return Result
     */
    public function __set($key, $var)
    {
        $this->_rs[$key] = $var;

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->_rs[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_rs[$key]);
    }

    /**
     * @param $key
     * @return Result
     */
    public function __unset($key)
    {
        unset($this->_rs[$key]);

        return $this;
    }

    /**
     * @param $key
     * @param null|mixed $var
     * @return Result
     */
    public function resultSet($key, $var = null)
    {
        if (\is_array($key))
            $this->_rs = $key + $this->_rs;
        elseif ($key)
            $this->__set($key, $var);

        return $this;
    }

    /**
     * @param null|mixed $key
     * @return array|mixed
     */
    public function resultGet($key = null)
    {
        return $key === null ? $this->_rs : $this->__get($key);
    }

    /**
     * @param $key
     * @return Result
     */
    public function resultDelete($key)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $v)
                $this->__unset($k);
        } else
            $this->__unset($key);

        return $this;
    }

    /**
     * @return Result
     */
    public function resultFlush()
    {
        $this->_rs = array();

        return $this;
    }

    // Iterator Methods

    /**
     * @return mixed
     */
    public function rewind()
    {
        return \reset($this->_rs);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return \current($this->_rs);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return \key($this->_rs);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return \next($this->_rs);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    // Countable Methods

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->_rs);
    }

    // ArrayAccess Methods

    /**
     * @param $key
     * @param $var
     * @return Result
     */
    public function offsetSet($key, $var)
    {
        return $this->__set($key, $var);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    /**
     * @param $key
     * @return Result
     */
    public function offsetUnset($key)
    {
        return $this->__unset($key);
    }
}