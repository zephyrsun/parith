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

use \Parith\App;

class Redis extends Basic
{
    static protected $ins_n = 0;
    static protected $ins_link = array();

    public $options = array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.0,
        'password' => '',
    );

    /**
     * @param $options
     * @return \Redis
     * @throws \Exception
     */
    public function dial($options)
    {
        if (is_array($options))
            $key = implode(':', $options);
        else
            $options = App::getOption($key = $options);

        self::$ins_n++;

        if ($link = &self::$ins_link[$key])
            return $link;

        $link = new \Redis();

        $options += $this->options;

        $connected = $link->connect($options['host'], $options['port'], $options['timeout']);
        if (!$connected)
            throw new \Exception("Fail to connect: {$options['host']}:{$options['port']}");

        if ($options['password'])
            $link->auth($options['password']);

        //$link->setOption(\Redis::OPT_READ_TIMEOUT, -1);

        return $link;
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