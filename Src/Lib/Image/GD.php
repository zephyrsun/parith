<?php

/**
 * Based on GD
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Lib\Image;

class GD extends Basic
{
    public function __construct($image = null)
    {
        if ($image)
            $this->loadImage($image);
    }

    public function __destruct()
    {
        if ($this->image)
            imagedestroy($this->image);
    }

    /**
     * @param $image
     * @return Image
     */
    protected function setImageData($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @static
     * @param $width
     * @param $height
     * @return resource
     */
    protected function create($width, $height)
    {
        $image = imagecreatetruecolor($width, $height);

        //imagecolortransparent($image, imagecolorallocate($image, 0, 0, 0));
        //$transparent = imagecolorallocatealpha($this->_image, 0, 0, 0, 127);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * @param $image
     * @return bool
     */
    public function loadImage($image)
    {
        $image = $this->_loadImage($image);

        if ($image) {
            $this->setImageData($image);
            return true;
        }

        return false;
    }

    private function _loadImage($image)
    {
        $ext = $this->getExtension($image);
        if (isset(static::$image_types[$ext])) {
            $call = 'imagecreatefrom' . static::$image_types[$ext];
            return @$call($image);
        }

        return @imagecreatefromstring($image);
    }

    public function width()
    {
        return imagesx($this->image);
    }

    public function height()
    {
        return imagesy($this->image);
    }

    /**
     * @param $width
     * @param $height
     * @param bool $center
     * @return GD
     */
    public function resize($width, $height, $center = false)
    {
        $src_w = $this->width();
        $src_h = $this->height();

        $src_x = 0;
        $src_y = 0;

        if ($center) {
            $ratio_w = $src_w / $width;
            $ratio_h = $src_h / $height;

            if ($ratio_w > $ratio_h) {
                $src_x = $src_w - $ratio_h * $width;

                $src_w -= $src_x;
                $src_x /= 2;
            } else {
                $src_y = $src_h - $ratio_w * $height;

                $src_h -= $src_y;
                $src_y /= 2;
            }
        }

        $image = $this->create($width, $height);

        // Crop and resize image
        imagecopyresampled($image, $this->image, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);

        return $this->setImageData($image);
    }

    /**
     * @param $width
     * @param $height
     * @param $src_x
     * @param $src_y
     * @return GD
     */
    public function crop($width, $height, $src_x, $src_y)
    {
        //$src_w = $this->width();
        //$src_h = $this->height();

        $image = $this->create($width, $height);

        // Crop and resize image
        imagecopyresampled($image, $this->image, 0, 0, $src_x, $src_y, $width, $height, $width, $height);

        return $this->setImageData($image);
    }

    /**
     * @param $angle
     * @param int $background
     * @return GD
     */
    public function rotate($angle, $background = 127)
    {
        // black background
        $transparent = imagecolorallocatealpha($this->image, 0, 0, 0, $background);

        // Rotate, setting the transparent color
        $image = imagerotate($this->image, 360 - $angle, $transparent, 1);

        $width = imagesx($image);
        $height = imagesy($image);

        // Save the alpha of the rotated image
        imagesavealpha($image, true);

        imagecopy($this->image, $image, 0, 0, 0, 0, $width, $height);

        return $this->setImageData($image);
    }

    /**
     * @param $image
     * @param int $x
     * @param int $y
     * @return GD
     */
    public function watermark($image, $x = 0, $y = 0)
    {
        $image = $this->_loadImage($image);

        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        imagealphablending($this->image, true);

        imagecopy($this->image, $image, $x, $y, 0, 0, $width, $height);

        imagedestroy($image);

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
        return (bool)$this->doSave($filename, $quality);
    }

    /**
     * @param $type
     * @param null $quality
     * @param bool $render
     * @return bool|string
     */
    public function export($type, $quality = null, $render = true)
    {
        if ($render) {
            \header('Content-Type: image/' . $type);

            $ext = $this->doSave($type, $quality);
            if ($ext)
                return true;

        } else {
            ob_start();
            if ($this->doSave($type, $quality))
                return ob_get_clean();
        }

        return false;
    }

    protected function doSave($filename, $quality)
    {
        if (!$this->image)
            return false;

        $ext = $this->getExtension($filename);

        if (!$ext) {
            $ext = $filename;
            $filename = null;
        }

        $types = static::$image_types;
        $ext = &$types[$ext];

        if ($ext)
            $call = 'image' . $ext;
        else
            return false;

        if ($quality === null)
            $call($this->image, $filename);
        else
            $call($this->image, $filename, $quality);

        return $ext;
    }
}