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

use \Parith\Result;

class View extends Result
{
    public $options = [
        'source_dir' => '',
        'source_ext' => 'php',
    ];

    /**
     * Basic constructor.
     */
    public function __construct()
    {
        $this->setOptions(\Parith::getOption('view'));
    }

    public function setOptions($options)
    {
        parent::setOptions($options);

        if (!$this->options['source_dir'])
            $this->options['source_dir'] = \APP_DIR . 'View';
    }

    /**
     * @param $__
     */
    public function render($__)
    {
        $__ = $this->getSourceFile($__);

        \extract($this->toArray(), EXTR_SKIP);

        include $__;
    }

    /**
     * @param $key
     * @param null $val
     * @return View
     */
    public function assign($key, $val = null)
    {
        $this->set($key, $val);

        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    public function fetch($name)
    {
        \ob_start();
        $this->render($name);
        return \ob_get_clean();
    }

    /**
     * @param $name
     * @return string
     * @throws \Exception
     */
    public function getSourceFile($name)
    {
        $name = $this->options['source_dir'] . \DIRECTORY_SEPARATOR . $name . '.' . $this->options['source_ext'];

        if (\is_file($name))
            return $name;

        throw new \Exception("View file '$name' not found");
    }
}