<?php

/**
 * File
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Lib;

class File extends \Parith\Object
{
    /**
     * @static
     * @param string $dir
     * @param string $mode
     * @return bool
     */
    public static function mkdir($dir, $mode = '0777')
    {
        if (\is_dir($dir))
            return true;

        if (self::mkdir(\dirname($dir), $mode))
            return \mkdir($dir, $mode) or \Parith\Monitor::addLog('Failed to make directory: ' . $dir);
    }

    /**
     * @static
     * @param string $filename
     * @return bool
     */
    public static function rm($filename)
    {
        if (\is_dir($filename) && $dh = \opendir($filename)) {
            while (false !== ($file = \readdir($dh)))
                if ($file !== '.' && $file !== '..')
                    self::rm($filename . DIRECTORY_SEPARATOR . $file);

            \closedir($dh);

            return @\rmdir($filename);
        }

        return @\unlink($filename);
    }

    /**
     * @static
     * @param string $dir
     * @param bool $r
     * @return array|bool
     */
    public static function ls($dir, $r = false)
    {
        if (\is_dir($dir) && $dh = \opendir($dir)) {
            $out = array();
            while (false !== ($file = \readdir($dh))) {
                if ($file != '.' && $file != '..') {
                    $file = $dir . DIRECTORY_SEPARATOR . $file;

                    if (\is_file($file))
                        $out[] = $file;
                    elseif ($r && $sub = self::ls($file, $r))
                        $out = $sub + $out;
                }
            }

            return $out;
        }

        return false;
    }

    /**
     * @static
     * @param $filename
     * @param mixed $val
     * @param bool $php_code
     * @return int
     */
    public static function touch($filename, $val, $php_code = true)
    {
        if ($php_code)
            $val = "<?php\n  return " . \var_export($val, true) . ";\n?>";

        return \file_put_contents($filename, $val);
    }

    /**
     * @static
     * @param string $filename
     * @return bool|string
     */
    public static function get($filename)
    {
        if (\is_file($filename))
            return \file_get_contents($filename);

        return false;
    }

    /**
     * @static
     * @param string $file1
     * @param string $file2
     * @return bool
     */
    public static function isNewer($file1, $file2)
    {
        return !\is_file($file2) || \filemtime($file1) > \filemtime($file2);
    }
}