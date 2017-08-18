<?php

/**
 * File
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

use \Parith\Result;

class File
{
    /**
     * @param $dir
     * @param int $mode
     * @return bool
     */
    static public function mkdir($dir, $mode = 0755)
    {
        if (\is_dir($dir))
            return true;

        if (static::mkdir(\dirname($dir), $mode)) {
            return \mkdir($dir, $mode);
        }
    }

    /**
     * @param $filename
     * @return bool
     */
    static public function rm($filename)
    {
        if (\is_dir($filename) && $dh = \opendir($filename)) {
            while (false !== ($file = \readdir($dh)))
                if ($file !== '.' && $file !== '..')
                    self::rm($filename . \DIRECTORY_SEPARATOR . $file);

            \closedir($dh);

            return @\rmdir($filename);
        }

        return @\unlink($filename);
    }

    /**
     * @param $dir
     * @param bool $r
     * @param array $ret
     * @return array
     */
    static public function ls($dir, $r = false, $ret = [])
    {
        if (\is_dir($dir) && $dh = \opendir($dir)) {
            while (false != ($file = \readdir($dh))) {
                if ($file != '.' && $file != '..') {
                    $file = $dir . \DIRECTORY_SEPARATOR . $file;

                    if (\is_file($file))
                        $ret[] = $file;
                    elseif ($r && $sub = self::ls($file, $r, $ret))
                        $ret = $sub + $ret;
                }
            }
        }

        return $ret;
    }

    /**
     * @param $filename
     * @param $data
     * @param bool $php_code
     * @return int
     */
    static public function touch($filename, $data)
    {
        if (is_array($data)) {
            $n = PHP_EOL;
            $data = "<?php{$n}return " . var_export($data, true) . ";{$n}?>";
        }

        static::mkdir(dirname($filename));

        return file_put_contents($filename, $data);
    }

    /**
     * @param $filename
     * @param bool $include
     * @return bool|mixed|string
     */
    static public function get($filename, $include = false)
    {
        if (\is_file($filename))
            return $include ? include $filename : \file_get_contents($filename);

        return false;
    }

    /**
     * @param $file1
     * @param $file2
     * @return bool
     */
    static public function isNewer($file1, $file2)
    {
        return !\is_file($file2) || \filemtime($file1) > \filemtime($file2);
    }
}