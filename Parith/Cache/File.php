<?php

/**
 * File Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Cache;

use \Parith\Lib\File as ParithFile;

class File extends Cache
{
    public $options = array(
        'dir' => '',
        'file_ext' => 'php',
    );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->options['dir'] or $this->options['dir'] = APP_DIR . 'tmp' . DIRECTORY_SEPARATOR . 'file';
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
        return ParithFile::touch($this->filename($name), $val);
    }

    /**
     * @param $name
     * @return bool
     */
    public function delete($name)
    {
        return ParithFile::rm($this->filename($name));
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return ParithFile::rm($this->options['dir']);
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