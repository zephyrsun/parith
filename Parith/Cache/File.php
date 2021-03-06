<?php

/**
 * File Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Cache;

use \Parith\Lib\File as LibFile;

class File extends Cache
{
    public $options = [
        'dir' => '',
        'file_ext' => 'php',
    ];

    public function setOptions($options)
    {
        parent::setOptions($options);

        if (!$this->options['dir'])
            $this->options['dir'] = \APP_DIR . 'tmp' . \DIRECTORY_SEPARATOR . 'file';
    }

    /**
     * @param $name
     *
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
     * @param $value
     *
     * @return bool|int
     */
    public function set($name, $value)
    {
        return LibFile::touch($this->filename($name), $value);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function delete($name)
    {
        return LibFile::rm($this->filename($name));
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return LibFile::rm($this->options['dir']);
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function filename($name)
    {
        return $this->options['dir'] . \DIRECTORY_SEPARATOR . $name . '.' . $this->options['file_ext'];
    }

}