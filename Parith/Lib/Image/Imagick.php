<?php

/**
 * Based on Imagick
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

class Imagick extends Image
{
    public $imagick;

    public function __construct($image = null)
    {
        $this->imagick = new \Imagick();

        if ($image)
            $this->load($image);
    }

    public function __destruct()
    {
        $this->imagick->clear();
        $this->imagick->destroy();
    }

    /**
     * @param $image
     * @param string $type
     * @return bool
     */
    public function load($image, $type = '')
    {
        return $this->_load($image, $type);
    }

    /**
     * @param $image
     * @param string $type
     * @return bool
     */
    private function _load($image, $type = '')
    {
        $type or $type = $this->getExtension($image);
        if ($type)
            return $this->imagick->readImage($image);

        return $this->imagick->readImageBlob($image);
    }

    public function width()
    {
        return $this->imagick->getImageWidth();
    }

    public function height()
    {
        return $this->imagick->getImageHeight();
    }

    /**
     * @param      $width
     * @param      $height
     * @param bool $center
     *
     * @return Imagick
     */
    public function resize($width, $height, $center = false)
    {
        if ($center) {
            $src_w = $this->width();
            $src_h = $this->height();

            list($src_w, $src_h, $src_x, $src_y) = $this->calcCenter($src_w, $src_h, $width, $height);

            $this->crop($src_w, $src_h, $src_x, $src_y);
        }

        $this->imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, false);

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @param $src_x
     * @param $src_y
     *
     * @return Imagick
     */
    public function crop($width, $height, $src_x, $src_y)
    {
        $this->imagick->cropImage($width, $height, $src_x, $src_y);

        return $this;
    }

    /**
     * @param        $angle
     * @param string $background
     *                      - '#FFF'
     *                      - new \ImagickPixel('transparent')
     *
     * @return Imagick
     */
    public function rotate($angle, $background = '#FFF')
    {
        $this->imagick->rotateImage($background, $angle);

        return $this;
    }

    /**
     * @param       $x
     * @param       $y
     * @param       $width  if <= 0, use the width of $this->image
     * @param       $height if <= 0, use the height of $this->image
     * @param array $color
     *
     * @return Imagick
     */
    public function overlay($x, $y, $width, $height, array $color = [])
    {
        $src_w = $this->width();
        $src_h = $this->height();

        if ($x < 0)
            $x += $src_w;

        if ($y < 0)
            $y += $src_h;

        $width = $width > 0 ? $x + $width : $src_w;

        $height = $height > 0 ? $y + $height : $src_h;

        $image = $this->create($width, $height);

        $color += [255, 255, 255];
        $color = imagecolorallocate($image, $color[0], $color[1], $color[2]);

        imagefilledrectangle($this->image, $x, $y, $width, $height, $color);

        imagedestroy($image);

        return $this;
    }

    /**
     * @param     $image
     * @param int $x
     * @param int $y
     *
     * @return Imagick
     */
    public function watermark($image, $x = 0, $y = 0)
    {
        $imagick = new \Imagick($image);

        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_OPAQUE);

        // Apply the watermark to the image
        $this->imagick->compositeImage($imagick, \Imagick::COMPOSITE_DISSOLVE, $x, $y);

        return $this;
    }

    /**
     * @param      $filename
     *                  - full filename, will save to a file
     *                  - jpg/png/gif, will trigger render mode
     *
     * @param null $quality
     *
     * @return bool
     */
    public function save($filename, $quality = null)
    {
        if ($this->prepareSave($filename, $quality))
            return $this->imagick->writeImage($filename);

        return false;
    }

    /**
     * @param      $type
     * @param null $quality
     * @param bool $render
     *
     * @return bool|string
     */
    public function export($type, $quality = 60, $render = true)
    {
        $ext = $this->prepareSave($type, $quality);
        if (!$ext)
            return false;

        $blob = $this->imagick->getImageBlob();

        if ($render) {
            \header('Content-Type: image/' . $ext);
            echo $blob;
            return true;
        }

        return $blob;
    }

    protected function prepareSave($type, $quality)
    {
        if (!$ext = $this->getExtension($type))
            $ext = $type;

        $types = static::$image_types;

        if ($ext = &$types[$ext])
            $this->imagick->setFormat($ext);
        else
            return false;

        if ($quality !== null)
            $this->imagick->setImageCompressionQuality($quality);

        return $ext;
    }
}