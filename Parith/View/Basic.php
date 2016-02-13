<?php

/**
 * Basic View
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\View;

use \Parith\App;
use \Parith\Result;

class Basic extends Result
{
    public $options = array(
        'source_dir' => '',
        'source_ext' => 'php',
    );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + App::getOption('view') + $this->options;

        if (!$this->options['source_dir'])
            $this->options['source_dir'] = \APP_DIR . 'View';
    }

    /**
     * @param $name
     * @param string $ext
     */
    public function render($name, $ext = '')
    {
        $name = $this->getSourceFile($name, $ext);

        \extract($this->resultGet(), EXTR_SKIP);

        include $name;
    }

    /**
     * @param $key
     * @param null $val
     * @return $this
     */
    public function assign($key, $val = null)
    {
        parent::resultSet($key, $val);

        return $this;
    }

    /**
     * @param $name
     * @param null $ext
     * @return string
     */
    public function fetch($name, $ext = null)
    {
        \ob_start();
        $this->render($name, $ext);
        return \ob_get_clean();
    }

    /**
     * @param $name
     * @param $ext
     * @return string
     * @throws \Exception
     */
    public function getSourceFile($name, $ext)
    {
        if (!$ext)
            $ext = $this->options['source_ext'];

        $name = $this->options['source_dir'] . \DIRECTORY_SEPARATOR . $name . '.' . $ext;

        if (\is_file($name))
            return $name;

        throw new \Exception("View file '$name' not found");
    }
}