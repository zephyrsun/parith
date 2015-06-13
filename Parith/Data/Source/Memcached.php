<?php
/**
 * Memcached
 * User: eagle
 * Date: 14-9-19
 * Time: 下午3:09
 */
namespace Parith\Data\Source;

class Memcached extends \Parith\Data\Source
{
    public $options = array(
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 0,
    );

    public $server_options = array(
        \Memcached::OPT_COMPRESSION => true,
        \Memcached::OPT_SERIALIZER => \Memcached::SERIALIZER_PHP, //\Memcached::SERIALIZER_IGBINARY
        \Memcached::OPT_HASH => \Memcached::HASH_DEFAULT,
        \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
        \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
    );

    /**
     * @var \Memcached
     */
    public $link;

    /**
     * @param array $servers = array(
     * array('host' => '192.168.1.1', 'port' => 11211),
     * array('host' => '192.168.1.2', 'port' => 11211),
     * );
     */
    protected function __construct($servers)
    {
        $this->connect($servers);
    }

    /**
     * @param $servers
     * @return $this
     * @throws \Exception
     */
    public function connect($servers)
    {
        $this->link = new \Memcached();

        foreach ($servers as $options) {
            $options += $this->options;

            $ret = $this->link->addServer($options['host'], $options['port'], $options['weight']);

            if (!$ret)
                throw new \Exception("Fail to connect: {$options['host']}:{$options['port']}");
        }

        $this->link->setOptions($this->server_options);

        return $this;
    }

    /**
     * @param string $key
     * @param callable $cache_cb
     * @param float $cas_token
     *
     * @return mixed
     */
    public function get($key, callable $cache_cb = null, &$cas_token = 0.0)
    {
        return $this->link->get($key, $cache_cb, $cas_token);
    }

    /**
     * @param array $keys
     * @param int $flags
     * @param array $cas_tokens
     *
     * @return mixed
     */
    public function getMulti(array $keys, $flags = \Memcached::GET_PRESERVE_ORDER, array &$cas_tokens = array())
    {
        return $this->link->getMulti($keys, $cas_tokens, $flags);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return bool
     */
    public function set($key, $value, $expiration = 0)
    {
        return $this->link->set($key, $value, $expiration);
    }

    /**
     * @param array $items
     * @param int $expiration
     *
     * @return bool
     */
    public function setMulti(array $items, $expiration = 0)
    {
        return $this->link->setMulti($items, $expiration);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return bool
     */
    public function add($key, $value, $expiration = 0)
    {
        return $this->link->add($key, $value, $expiration);
    }

    /**
     * @param string $key
     * @param int $time
     *
     * @return bool
     */
    public function delete($key, $time = 0)
    {
        return $this->link->delete($key, $time);
    }

    /**
     * @param array $keys
     * @param int $time
     */
    public function deleteMulti(array $keys, $time = 0)
    {
        return $this->link->deleteMulti($keys, $time);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return mixed
     */
    public function replace($key, $value, $expiration = 0)
    {
        return $this->link->replace($key, $value, $expiration);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function prepend($key, $value)
    {
        return $this->link->prepend($key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function append($key, $value)
    {
        return $this->link->append($key, $value);
    }

    /**
     * @param string $key
     * @param int $offset
     *
     * @return int
     */
    public function increment($key, $offset = 1)
    {
        return $this->link->increment($key, $offset);
    }

    /**
     * @param string $key
     * @param int $offset
     *
     * @return int
     */
    public function decrement($key, $offset = 1)
    {
        return $this->link->decrement($key, $offset);
    }

    /**
     * @param int $delay
     *
     * @return bool
     */
    public function flush($delay = 0)
    {
        return $this->link->flush($delay);
    }

    /**
     * @return $this
     */
    public function close()
    {
        $this->link->quit();

        return $this;
    }
} 