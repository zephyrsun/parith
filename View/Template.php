<?php

/**
 * Template
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\View;

use \Parith\Lib\File as LibFile;

class Template extends Basic
{
    public $cache, $options = array(
        'source_dir' => null,
        'source_ext' => 'html',
        'cache_dir' => null,
        'ldelim' => '{',
        'rdelim' => '}',
    );


    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $dir = $this->options['cache_dir'] or $dir = APP_DIR . 'tmp' . DIRECTORY_SEPARATOR . 'template';

        $this->cache = new \Parith\Cache\File(array('dir' => $dir));
    }

    /**
     * @param $name
     * @param string $ext
     */
    public function render($name, $ext = '')
    {
        $source = $this->getSourceFile($name, $ext);

        $target = $this->cache->filename(\rawurlencode($name));
        if (LibFile::isNewer($source, $target))
            LibFile::touch($target, self::parse(\file_get_contents($source), $this->options['ldelim'], $this->options['rdelim']), false);

        include $target;
    }

    /**
     * @static
     * @param $tag
     * @param $ldelim
     * @param $rdelim
     * @return mixed
     */
    public static function parse($tag, $ldelim, $rdelim)
    {
        return \preg_replace_callback(
            '/' . $ldelim . '([^' . $ldelim . $rdelim . ']+)' . $rdelim . '/', //  '/{([^{}]+)}/'
            '\Parith\View\Template::parseBrace', $tag //\stripslashes($tag)
        );
    }

    /**
     * @static
     * @param $str
     * @return mixed|string
     */
    public static function parseBrace($str)
    {
        $p = $r = array();

        // {if $foo}
        $p[] = '/^if\s+(.+)$/';
        $r[] = '<?php if(\\1) { ?>';

        // {else}
        $p[] = '/^else$/';
        $r[] = '<?php } else { ?>';

        // {elseif}
        $p[] = '/^elseif\s+(.+?)$/';
        $r[] = '<?php } elseif (\\1) { ?>';

        // {foreach $name as $key => $val}
        $p[] = '/^foreach\s+(\S+)\s+as\s+(\S+(\s*=>\s*\S+)?)$/';
        $r[] = '<?php if(\is_array(\\1)) foreach(\\1 as \\2) { ?>';

        // {while $a}
        $p[] = '/^while\s+(\S+)$/';
        $r[] = '<?php while (\\1) { ?>';

        // {break}
        $p[] = '/^break$/';
        $r[] = '<?php break; ?>';

        // {continue}
        $p[] = '/^continue(\s+\d+)?$/';
        $r[] = '<?php continue\\1; ?>';

        // ending
        $p[] = '/^(\/if|\/foreach|\/while)$/';
        $r[] = '<?php } ?>';

        //variable {$foo}, {\App::$foo}
        //const {PARITH_DIR}, {\App::PARITH_DIR}
        //method {date('Y-m-d', \APP_TS)}, {\Router::path()}
        $p[] = '/^(.+::)?(\$\w+[^\s}]*|[A-Z_]*|[^\(\s]+\(.*\))$/';
        $r[] = '<?php echo \\0; ?>';

        $s = \preg_replace($p, $r, $str[1]);

        // parse vars
        $s = \preg_replace_callback('/(?<!::)\$[^\d\s}\(\)]+/', '\Parith\View\Template::_parseVar', $s);

        // parse include
        $s = \preg_replace_callback('/^include\s+([^}]+)$/', '\Parith\View\Template::_parseInclude', $s);

        // for js, css
        if ($s === $str[1])
            $s = '{' . $s . '}';

        return $s;
    }

    /**
     * @static
     * @param $val
     * @return mixed
     */
    private static function _parseVar($val)
    {
        return \preg_replace(
            array(
                '/\.(\w+)/', // replace $foo.bar.str to $foo['bar']['str']
                '/\$(?!this->)(\w+)/'),
            array(
                "['\\1']",
                '$this->\\1'
            ),
            $val[0]);
    }

    /**
     * @static
     * @param $val
     * @return string
     */
    private static function _parseInclude($val)
    {
        return '<?php $this->load(' . self::propExport($val[1]) . '); ?>';
    }

    /**
     * @static
     * @param $str
     * @return string
     */
    public static function propExport($str)
    {
        // \$[^=\s]+ : variables
        // \'[^\']*\' : single quoted string
        // "[^"]*" : double quoted string
        // [^"\'=\s]+ : other string
        //\preg_match_all('/([^=\s]+)=(\'[^\']+\'|"[^"]+"|\S+)/', $str, $match, PREG_SET_ORDER);

        return 'array(' . \preg_replace('/([^=\s]+)(=)(\'[^\']+\'|"[^"]+"|\S+)/', "'\\1'\\2>\\3,", $str) . ')';
    }

    /**
     * @param $data
     * @return Template
     */
    public function load($data)
    {
        $this->resultSet($data);

        //$this->render($this->_data['file']);
        $this->render($this->file, $this->ext);

        // avoid collision
        parent::resultDelete($data);

        return $this;
    }
}