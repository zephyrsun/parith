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

namespace Parith\Lib\Image;

abstract class Basic extends \Parith\Object
{
    public static $image_types = array(
        'jpg' => 'jpeg',
        'jpeg' => 'jpeg',
        'png' => 'png',
        'gif' => 'gif',
    );

    protected $image;

    public function getExtension($filename)
    {
        $ext = explode('?', pathinfo($filename, PATHINFO_EXTENSION), 2);
        return $ext[0];
    }

    abstract public function width();

    abstract public function height();

    abstract public function resize($width, $height, $center = false);

    abstract public function crop($width, $height, $src_x, $src_y);

    abstract public function rotate($angle, $background = 0);

    abstract public function save($filename, $quality = null);

    abstract public function export($type, $quality = null, $render = true);

    abstract public function watermark($image, $x = 0, $y = 0);
}