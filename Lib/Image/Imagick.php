<?php

/**
 * Based on Imagick
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

class Imagick extends \Parith\Lib\Image
{
    public $lib;

    public function __construct($image = null)
    {
        $this->lib = new \Imagick();

        if ($image)
            $this->loadImage($image);
    }

    public function __destruct()
    {
        $this->lib->clear();
        $this->lib->destroy();
    }

    /**
     * @param $image
     * @return Imagick
     */
    public function loadImage($image)
    {
        $ext = $this->getExtension($image);
        if (isset(static::$image_types[$ext])) {
            $this->lib->readImage($image);
        } else {
            $this->lib->readImageBlob($image);
        }

        return $this;
    }

    public function width()
    {
        return $this->lib->getImageWidth();
    }

    public function height()
    {
        return $this->lib->getImageHeight();
    }

    /**
     * @param $width
     * @param $height
     * @param bool $center
     * @return Imagick
     */
    public function resize($width, $height, $center = false)
    {
        if ($center) {
            $src_w = $this->width();
            $src_h = $this->height();

            $ratio_w = $src_w / $width;
            $ratio_h = $src_h / $height;

            if ($ratio_w > $ratio_h) {
                $src_x = $src_w - $ratio_h * $width;
                $src_y = 0;
            } else {
                $src_x = 0;
                $src_y = $src_h - $ratio_w * $height;
            }

            $this->crop($src_w - $src_x, $src_h - $src_y, $src_x / 2, $src_y / 2);
        }

        $this->lib->resizeimage($width, $height, \Imagick::FILTER_LANCZOS, 1, false);

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @param $src_x
     * @param $src_y
     * @return Imagick
     */
    public function crop($width, $height, $src_x, $src_y)
    {
        $this->lib->cropImage($width, $height, $src_x, $src_y);

        return $this;
    }

    /**
     * @param $angle
     * @param string $background
     *                      - '#FFF'
     *                      - new \ImagickPixel('transparent')
     * @return Imagick
     */
    public function rotate($angle, $background = '#FFF')
    {
        $this->lib->rotateImage($background, $angle);

        return $this;
    }

    /**
     * @param $filename
     *                  - full filename, will save to a file
     *                  - jpg/png/gif, will trigger render mode
     *
     * @param null $quality
     * @return bool
     */
    public function save($filename, $quality = null)
    {
        if ($this->saveSetup($filename, $quality))
            return $this->lib->writeImage($filename);

        return false;
    }

    /**
     * according to save
     *
     * @param $type
     * @param null $quality
     * @return bool
     */
    public function render($type, $quality = null)
    {
        $ext = $this->saveSetup($type, $quality);
        if ($ext) {
            \header('Content-Type: image/' . $ext);
            echo $this->lib->getImageBlob();
        }
    }

    protected function saveSetup($filename, $quality)
    {
        $ext = $this->getExtension($filename) or $ext = $filename;

        $types = static::$image_types;
        $ext = &$types[$ext];

        if ($ext)
            $this->lib->setFormat($ext);
        else
            return false;

        $quality === null or $this->lib->setImageCompressionQuality($quality);

        return $ext;
    }
}