<?php

/**
 * Memcache
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\DataSource;

use \Parith\App;

class Memcache extends Basic
{
    static protected $ins_n = 0;
    static protected $ins_link = array();

    public $options = array(
        'host' => '127.0.0.1',
        'port' => 11211,
        'timeout' => 1,
        'persistent' => true,
        'weight' => 1,
        'retry_interval' => 15,
        'status' => true,
        'failure_callback' => null,
    );

    /**
     * @param $servers
     *          array(
     *              array('host' => '192.168.1.1', 'port' => 11211),
     *              array('host' => '192.168.1.2', 'port' => 11211),
     *          );
     * @return \Memcache
     * @throws \Exception
     */
    public function dial($servers)
    {
        if (is_array($servers))
            $key = implode(':', current($servers));
        else
            $servers = App::getOption($key = $servers);

        if ($link = &self::$ins_link[$key])
            return $link;

        $link = new \Memcache();

        foreach ($servers as $o) {
            $o += $this->options; //$this->option($options);

            $r = $link->addServer(
                $o['host'],
                $o['port'],
                $o['persistent'],
                $o['weight'],
                $o['timeout'],
                $o['retry_interval'],
                $o['status'],
                $o['failure_callback']
            );

            if (!$r)
                throw new \Exception("Fail to connect: {$o['host']}:{$o['port']}");
        }

        static::$ins_n++;

        return $link;
    }

    public function closeAll()
    {
        /**
         * @var $link \Memcache
         */
        foreach (self::$ins_link as $link)
            $link->close();
    }
}