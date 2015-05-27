<?php

/**
 * Redis
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Data\Source;

use \Parith\Data\Source;

class Redis extends Source
{
    public $options = array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.0,
    );

    /**
     * @var \Redis
     */
    public $link;

    public static $_pool = array();

    public function __construct(array $options = array())
    {
        $this->option($options);
        $this->connect();
    }

    /**
     * @return \Redis
     * @throws \Exception
     */
    protected function getLink()
    {
        $this->link = new \Redis();

        $options = & $this->options;

        $connected = $this->link->connect($options['host'], $options['port'], $options['timeout']);
        if (!$connected)
            throw new \Exception("Fail to connect: {$options['host']}:{$options['port']}");

        //$this->link->setOption(\Redis::OPT_READ_TIMEOUT, -1);

        return $this->link;
    }

    /**
     * @return $this
     */
    public function connect()
    {
        $k = $this->options['host'] . ':' . $this->options['port'];

        if (isset(self::$_pool[$k])) {
            $this->link = self::$_pool[$k];
        } else {
            $this->link = self::$_pool[$k] = $this->getLink();
        }

        return $this;
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function call($method, $args)
    {
        return \call_user_func_array(array($this->link, $method), $args);
    }

    /**
     * @return Redis
     */
    public function close()
    {
        $this->link->close();

        return $this;
    }

    public static function closeAll()
    {
        foreach (self::$_pool as $link) {
            $link->close();
        }
    }
}