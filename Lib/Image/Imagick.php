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
 * @link http://www.parith.net/
 */

namespace Parith\Lib\Image;

class Imagick extends Basic
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
        return $this->_loadImage($image, $this->lib);
    }

    private function _loadImage($image, &$lib)
    {
        $ext = $this->getExtension($image);
        if (isset(static::$image_types[$ext])) {
            return $lib->readImage($image);
        } else {
            return $lib->readImageBlob($image);
        }
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
     * @param $x
     * @param $y
     * @param $width if <= 0, use the width of $this->image
     * @param $height if <= 0, use the height of $this->image
     * @param array $color
     * @return Imagick
     */
    public function overlay($x, $y, $width, $height, array $color = array())
    {
        $src_w = $this->width();
        $src_h = $this->height();

        if ($x < 0)
            $x += $src_w;

        if ($y < 0)
            $y += $src_h;

        if ($width > 0) {
            $width = $x + $width;
        } else {
            $width = $src_w;
        }

        if ($height > 0) {
            $height = $y + $height;
        } else {
            $height = $src_h;
        }

        $image = $this->create($width, $height);

        $color += array(255, 255, 255);
        $color = imagecolorallocate($image, $color[0], $color[1], $color[2]);

        imagefilledrectangle($this->image, $x, $y, $width, $height, $color);

        imagedestroy($image);

        return $this;
    }

    /**
     * @param $image
     * @param int $x
     * @param int $y
     * @return Imagick
     */
    public function watermark($image, $x = 0, $y = 0)
    {
        $imagick = new \Imagick();
        $this->_loadImage($image, $imagick);

        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_OPAQUE);

        // Apply the watermark to the image
        $this->lib->compositeImage($imagick, \Imagick::COMPOSITE_DISSOLVE, $x, $y);

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
        if ($this->doSave($filename, $quality))
            return $this->lib->writeImage($filename);

        return false;
    }

    /**
     * @param $type
     * @param null $quality
     * @param bool $render
     * @return bool|string
     */
    public function export($type, $quality = null, $render = true)
    {
        $ext = $this->doSave($type, $quality);
        if (!$ext)
            return false;

        $blob = $this->lib->getImageBlob();

        if ($render) {
            \header('Content-Type: image/' . $ext);
            echo $blob;
            return true;
        }

        return $blob;
    }

    protected function doSave($filename, $quality)
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