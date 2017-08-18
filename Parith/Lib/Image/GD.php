<?php

/**
 * Based on GD
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

class GD extends Image
{
    public function __construct($image = null)
    {
        if ($image)
            $this->load($image);
    }

    public function __destruct()
    {
        if ($this->image)
            imagedestroy($this->image);
    }

    /**
     * @param $image
     * @return $this
     */
    protected function setImageData($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @static
     *
     * @param $width
     * @param $height
     *
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
     * @param string $type
     * @return bool
     */
    public function load($image, $type = '')
    {
        $image = $this->_load($image, $type);

        if ($image) {
            $this->setImageData($image);
            return true;
        }

        return false;
    }

    private function _load($image, $type = '')
    {
        $type or $type = $this->imageType($image);

        if ($type) {
            $call = 'imagecreatefrom' . $type;
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
     * @param      $width
     * @param      $height
     * @param bool $center
     *
     * @return GD
     */
    public function resize($width, $height, $center = false)
    {
        $src_w = $this->width();
        $src_h = $this->height();

        $src_x = 0;
        $src_y = 0;

        if ($center) {
            list($src_w, $src_h, $src_x, $src_y) = $this->calcCenter($src_w, $src_h, $width, $height);
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
     *
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
     * @param     $angle
     * @param int $background
     *
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
     * @param int $pos
     * @return $this
     */
    public function watermark($image, $pos = 0)
    {
        $image = $this->_load($image);

        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        imagealphablending($this->image, true);

        switch ($pos) {
            case self::POS_TOP_LEFT:
                $x = 0;
                $y = 0;
                break;
            case self::POS_TOP_RIGHT:
                $x = $this->width() - $width;
                $y = 0;
                break;
            case self::POS_BOTTOM_LEFT:
                $x = 0;
                $y = $this->height() - $height;
                break;
            case self::POS_BOTTOM_RIGHT:
                $x = $this->width() - $width;
                $y = $this->height() - $height;
                break;
            default:
                $x = ($this->width() - $width) / 2;
                $y = ($this->height() - $height) / 2;
        }

        imagecopy($this->image, $image, $x, $y, 0, 0, $width, $height);

        imagedestroy($image);

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
        return (bool)$this->prepareSave($filename, $quality);
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
        if ($render) {
            \header('Content-Type: image/' . $type);

            $ext = $this->prepareSave($type, $quality);
            if ($ext)
                return true;

        } else {
            ob_start();
            if ($this->prepareSave($type, $quality))
                return ob_get_clean();
        }

        return false;
    }

    protected function prepareSave($type, $quality)
    {
        if (!$this->image)
            return false;

        $ext = $this->getExtension($type);

        if (!$ext) {
            $ext = $type;
            $type = null;
        }

        $types = static::$image_types;

        if ($ext = &$types[$ext])
            $call = 'image' . $ext;
        else
            return false;

        if ($quality === null)
            $call($this->image, $type);
        else
            $call($this->image, $type, $quality);

        return $ext;
    }
}