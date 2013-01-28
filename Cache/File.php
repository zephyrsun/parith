<?php

/**
 * File Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith\Cache;

class File extends Cache
{
    public $options = array(
        'dir' => null,
        'file_ext' => 'php',
    );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if ($this->options['dir'] === null)
            $this->options['dir'] = APP_DIR . 'tmp' . DIRECTORY_SEPARATOR . 'file';

        //\Parith\Lib\File::mkdir($this->options['dir']);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        $name = $this->filename($name);

        if (\is_file($name))
            return include $name;

        return false;
    }

    /**
     * @param $name
     * @param $val
     * @return bool|int
     */
    public function set($name, $val)
    {
        return \Parith\Lib\File::touch($this->filename($name), $val);
    }

    /**
     * @param $name
     * @return bool
     */
    public function delete($name)
    {
        return \Parith\Lib\File::rm($this->filename($name));
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return \Parith\Lib\File::rm($this->options['dir']);
    }

    /**
     * @param $name
     * @return string
     */
    public function filename($name)
    {
        return $this->options['dir'] . DIRECTORY_SEPARATOR . $name . '.' . $this->options['file_ext'];
    }

}