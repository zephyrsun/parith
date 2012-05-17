<?php

/**
 * Data Source
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

namespace Parith\Data;

abstract class Source
{
    public $configs = array(), $options = array();

    protected $link;

    public function __construct(array $options = array())
    {
        if ($options)
            $this->connect($options);
    }

    /**
     * connect to server
     * @abstract
     * @param array $options
     */
    abstract public function connect($options = array());

    /**
     * disconnect from server
     * @abstract
     */
    abstract public function close();

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param $cfg
     * @param $key
     * @param array $options
     * @return array
     * @throws \Parith\Exception
     */
    public function getServer($cfg, $key = null, array $options = array())
    {
        if (is_array($cfg))
            return $cfg;
        elseif ($key === null)
            return \Parith\App::config($cfg, $options);

        $cfg = \Parith\App::config($cfg);

        $cfg = &$cfg[$key];

        if (is_array($cfg))
            return $cfg + $options;

        throw new \Parith\Exception('Incorrect config ID: ' . $key . ' in ' . get_called_class());
    }

    /**
     * @param array $options
     * @return array
     */
    public function option(array $options = array())
    {
        return $options + $this->options;
    }
}