<?php

/**
 * File Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2011 Zephyr Sun
 * @license http://www.parith.org/license
 * @version 0.3
 * @link http://www.parith.org/
 */

namespace Parith\Cache;

class File extends Cache
{
    public $default = array('dir' => null, 'file_ext' => 'php');

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options or parent::option('Cache_File', $options);

        $this->options['dir'] === null and $this->options['dir'] = APP_DIR . 'tmp' . DS . 'File';

        \Parith\Lib\File::mkdir($this->options['dir']);
    }

    /**
     * @param $name
     * @return bool|mixed
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
     * @param $var
     * @return bool
     */
    public function set($name, $var)
    {
        return \Parith\Lib\File::touch($this->filename($name), $var);
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
        return $this->options['dir'] . DS . $name . '.' . $this->options['file_ext'];
    }

}