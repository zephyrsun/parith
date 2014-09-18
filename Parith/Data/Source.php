<?php

/**
 * Data Source
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Data;

use \Parith\App;

abstract class Source
{
    public $options = array(
        'host' => '127.0.0.1',
        'port' => 0
    );

    public $configs = array(), $connected = false, $link;

    public static $pool = array();

    public function __construct(array $options = array())
    {
        if ($options) {
            $this->option($options);
            $this->connect();
        }
    }

    public function connect()
    {
        $this->link = & self::$pool[$this->instanceKey()] or $this->link = $this->getLink();

        return $this;
    }

    /**
     * disconnect from server
     * @abstract
     */
    abstract public function close();

    /**
     * @abstract
     * @return mixed
     */
    abstract protected function getLink();

    /**
     * singleton
     *
     * @static
     *
     * @param array $options
     *
     * @return \get_called_class
     */
    public static function singleton(array $options = array())
    {
        return App::getInstance(\get_called_class(), \func_get_args());
    }

    /**
     * @return string
     */
    public function instanceKey()
    {
        return $this->options['host'] . ':' . $this->options['port'];
    }

    /**
     * an Overwrite example:
     *
     * public static function option($cfg_id)
     * {
     *      $servers = array (
     *          1 => array('host' => '127.0.0.1', 'port' => 11211),
     *          2 => array('host' => '127.0.0.1', 'port' => 11212),
     *      );
     *
     *      return parent::option($servers[$cfg_id]);
     * }
     *
     * @static
     *
     * @param array $options
     *
     * @return \Parith\Data\Source
     */
    public function option(array $options)
    {
        $this->options = $options + $this->options;

        return $this;
    }
}