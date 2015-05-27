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

    /**
     * @param array $options
     * @return mixed
     */
    public static function singleton(array $options = array(), $key = '')
    {
        return App::getInstance(\get_called_class(), array($options), $key);
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