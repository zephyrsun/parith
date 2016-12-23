<?php
/**
 * Memcached
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


class Memcached extends DataSource
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
        'weight' => 0,
    ];

    public $server_options = [
        \Memcached::OPT_COMPRESSION => true,
        \Memcached::OPT_SERIALIZER => \Memcached::SERIALIZER_IGBINARY, //\Memcached::SERIALIZER_PHP,
        \Memcached::OPT_HASH => \Memcached::HASH_DEFAULT,
        \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
        \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
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
            $servers = \Parith::getOption($key = $servers);

        self::$ins_n++;

        if ($link = &self::$ins_link[$key]) {
            $this->link = $link;
        } else {
            $this->link = $link = new \Memcached();

            foreach ($servers as $o) {
                $o += $this->options;

                $ret = $link->addServer($o['host'], $o['port'], $o['weight']);

                if (!$ret)
                    throw new \Exception("Fail to connect: {$o['host']}:{$o['port']}");
            }

            $link->setOptions($this->server_options);
        }

        return $this;
    }

    public function closeAll()
    {
        /**
         * @var $link \Memcached
         */
        foreach (self::$ins_link as $link)
            $link->quit();
    }
} 