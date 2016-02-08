<?php

/**
 * File
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

use \Parith\Result;

class File extends Result
{
    /**
     * @static
     *
     * @param string $dir
     * @param string $mode
     *
     * @return bool
     */
    static public function mkdir($dir, $mode = '0644')
    {
        if (\is_dir($dir))
            return true;

        if (static::mkdir(\dirname($dir), $mode))
            return \mkdir($dir, $mode);
    }

    /**
     * @static
     *
     * @param string $filename
     *
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
     * @static
     *
     * @param string $dir
     * @param bool $r recursion
     *
     * @return array|bool
     */
    static public function ls($dir, $r = false)
    {
        if (\is_dir($dir) && $dh = \opendir($dir)) {
            $ret = array();
            while (false != ($file = \readdir($dh))) {
                if ($file != '.' && $file != '..') {
                    $file = $dir . \DIRECTORY_SEPARATOR . $file;

                    if (\is_file($file))
                        $ret[] = $file;
                    elseif ($r && $sub = self::ls($file, $r))
                        $ret = $sub + $ret;
                }
            }

            return $ret;
        }

        return false;
    }

    /**
     * @static
     *
     * @param       $filename
     * @param mixed $data
     * @param bool $php_code
     *
     * @return int
     */
    static public function touch($filename, $data, $php_code = false)
    {
        if ($php_code) {
            $n = PHP_EOL;
            $data = "<?php{$n}return " . var_export($data, true) . ";{$n}?>";
        }

        static::mkdir(dirname($filename));

        return file_put_contents($filename, $data);
    }

    /**
     * @param $filename
     * @param bool $include
     * @return mixed
     */
    static public function get($filename, $include = false)
    {
        if (\is_file($filename))
            return $include ? include $filename : \file_get_contents($filename);

        return false;
    }

    /**
     * @static
     *
     * @param string $file1
     * @param string $file2
     *
     * @return bool
     */
    static public function isNewer($file1, $file2)
    {
        return !\is_file($file2) || \filemtime($file1) > \filemtime($file2);
    }
}