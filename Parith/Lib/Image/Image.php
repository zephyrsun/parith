<?php

/**
 * Image
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib\Image;

use Parith\Result;

abstract class Image extends Result
{
    static public $image_types = [
        'jpg' => 'jpeg',
        'jpeg' => 'jpeg',
        'png' => 'png',
        'gif' => 'gif',
    ];

    protected $image;

    static public function getExtension($filename)
    {
        //$ext = explode('?', pathinfo($filename, PATHINFO_EXTENSION), 2);
        //return $ext[0];
        return preg_replace('/.+\.(\w+).*/', '$1', $filename);
        //return strtolower(strrchr($filename, '.'));
    }

    static public function imageType($filename)
    {
        $ext = self::getExtension($filename);

        return isset(static::$image_types[$ext]) ? static::$image_types[$ext] : '';
    }

    public function calcCenter($src_w, $src_h, $width, $height)
    {
        $ratio_w = $src_w / $width;
        $ratio_h = $src_h / $height;

        if ($ratio_w > $ratio_h) {
            $src_x = $src_w - $ratio_h * $width;
            $src_y = 0;
        } else {
            $src_x = 0;
            $src_y = $src_h - $ratio_w * $height;
        }

        return [$src_w - $src_x, $src_h - $src_y, $src_x / 2, $src_y / 2];
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