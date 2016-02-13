<?php

/**
 * Helper\HTML
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\View\Helper;

use \Parith\Result;

class HTML extends Result
{
    static public function tag($tag, $text = '', array $attributes = array())
    {
        if ($text === null)
            $text = ' />';
        else
            $text = '>' . $text . '</' . $tag . '>';

        return '<' . $tag . self::attributes($attributes) . $text;
    }

    static public function attributes(array $attributes)
    {
        $s = '';
        foreach ($attributes as $key => $str)
            $s .= ' ' . $key . '="' . self::entities($str) . '"';

        return $s;
    }

    static public function entities($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
    }

    static public function link($url, $text = '', array $attributes = array())
    {
        if (!isset($attributes['href']))
            $attributes['href'] = $url;

        return self::tag('a', $text, $attributes);
    }

    static public function select(array $options, $select = null, array $attributes = array())
    {
        return self::tag('select', self::option($options, $select), $attributes);
    }

    static public function selectNum($min, $max, $select = null, array $attributes = array())
    {
        $options = array();
        for (; $min <= $max; $min++)
            $options[$min] = $min;

        return self::tag('select', self::option($options, $select), $attributes);
    }

    static public function option(array $options, $selected)
    {
        $s = '';
        foreach ($options as $val => $option) {
            if (\is_array($option)) {
                $s .= self::tag('optgroup', self::option($option, $selected), array('label' => $val));
            } else {
                $attributes = array('value' => $val);

                if ($val == $selected)
                    $attributes['selected'] = 'selected';

                $s .= self::tag('option', $option, $attributes);
            }
        }

        return $s;
    }
}