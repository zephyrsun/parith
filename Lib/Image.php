<?php

/**
 * Image
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

namespace Parith\Lib;

abstract class Image extends \Parith\Object
{
    public static $image_types = array(
        'jpg' => 'jpeg',
        'jpeg' => 'jpeg',
        'png' => 'png',
        'gif' => 'gif',
    );

    protected $image;

    abstract public function width();

    abstract public function height();

    abstract public function resize($width, $height, $center = false);

    abstract public function crop($width, $height, $src_x, $src_y);

    abstract public function rotate($angle, $background = 0);

    abstract public function save($image);

    abstract public function render($image);
}