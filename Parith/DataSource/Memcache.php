<?php

/**
 * Memcache
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\DataSource;


class Memcache
{
    static protected $ins_n = 0;
    static protected $ins_link = [];

    /**
     * @var \Memcached
     */
    public $link;

    public $options = [
        'host' => '127.0.0.1',
        'port' => 11211,
        'timeout' => 1,
        'persistent' => true,
        'weight' => 1,
        'retry_interval' => 15,
        'status' => true,
        'failure_callback' => null,
    ];

    /**
     * @param $servers
     *          [
     *              ['host' => '192.168.1.1', 'port' => 11211],
     *              ['host' => '192.168.1.2', 'port' => 11211],
     *          ];
     * @return $this
     * @throws \Exception
     */
    public function dial($servers)
    {
        if (is_array($servers))
            $key = implode(':', current($servers));
        else
            $servers = \Parith::getEnv($key = $servers);

        self::$ins_n++;

        if ($link = &self::$ins_link[$key]) {
            $this->link = $link;
        } else {
            $this->link = $link = new \Memcache();

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
        }

        return $this;
    }

    public function __destruct()
    {
        if (--static::$ins_n == 0) {
            /**
             * @var $link \Memcache
             */
            foreach (self::$ins_link as $link)
                $link->close();

            static::$ins_link = [];
        }
    }
}