<?php
/**
 * Memcached
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

class Memcached extends Basic
{
    static protected $ins_n = 0;
    static protected $ins_link = array();

    public $options = array(
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 0,
    );

    public $server_options = array(
        \Memcached::OPT_COMPRESSION => true,
        \Memcached::OPT_SERIALIZER => \Memcached::SERIALIZER_IGBINARY, //\Memcached::SERIALIZER_PHP,
        \Memcached::OPT_HASH => \Memcached::HASH_DEFAULT,
        \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
        \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
    );

    /**
     * @param $servers
     *          array(
     *              array('host' => '192.168.1.1', 'port' => 11211),
     *              array('host' => '192.168.1.2', 'port' => 11211),
     *          );
     * @return \Memcached
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

        $link = new \Memcached();

        foreach ($servers as $o) {
            $o += $this->options;

            $ret = $link->addServer($o['host'], $o['port'], $o['weight']);

            if (!$ret)
                throw new \Exception("Fail to connect: {$o['host']}:{$o['port']}");
        }

        $link->setOptions($this->server_options);

        return $link;
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