<?php

/**
 * Redis
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


class Redis extends DataSource
{
    static protected $ins_n = 0;
    static protected $ins_link = [];

    /**
     * @var \Redis
     */
    public $link;

    public $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.0,
        'password' => '',
    ];

    /**
     * @param $options
     * @return $this
     * @throws \Exception
     */
    public function dial($options)
    {
        if (is_array($options))
            $key = implode(':', $options);
        else
            $options = \Parith::getOption($key = $options);

        self::$ins_n++;

        if ($link = &self::$ins_link[$key]) {
            $this->link = $link;
        } else {
            $this->link = $link = new \Redis();

            $options += $this->options;

            $connected = $link->connect($options['host'], $options['port'], $options['timeout']);
            if (!$connected)
                throw new \Exception("Fail to connect: {$options['host']}:{$options['port']}");

//            if ($options['password'])
//                $link->auth($options['password']);

//$link->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        }

        return $this;
    }

    public function closeAll()
    {
        /**
         * @var $link \Redis
         */
        foreach (self::$ins_link as $link)
            $link->close();
    }
}