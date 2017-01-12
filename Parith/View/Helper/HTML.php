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
    static public function tag($tag, $text = '', array $attributes = [])
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

    static public function link($url, $text = '', array $attributes = [])
    {
        if (!isset($attributes['href']))
            $attributes['href'] = $url;

        return self::tag('a', $text, $attributes);
    }

    static public function radio(array $options, $checked = null, array $label_attr = [], array $input_attr = [])
    {
        return self::radioOption('radio', $options, $checked, $label_attr, $input_attr);
    }

    static public function checkbox(array $options, $checked = null, array $label_attr = [], array $input_attr = [])
    {
        return self::radioOption('checkbox', $options, $checked, $label_attr, $input_attr);
    }

    static public function select(array $options, $selected = null, array $attributes = [])
    {
        return self::tag('select', self::selectOption($options, $selected), $attributes);
    }

    static public function selectNum($min, $max, $select = null, array $attributes = [])
    {
        $options = [];
        for (; $min <= $max; $min++)
            $options[$min] = $min;

        return self::tag('select', self::selectOption($options, $select), $attributes);
    }

    static public function selectOption(array $options, $selected)
    {
        $s = '';
        foreach ($options as $val => $option) {
            if (\is_array($option)) {
                $s .= self::tag('optgroup', self::selectOption($option, $selected), ['label' => $val]);
            } else {
                $attributes = ['value' => $val];

                if ($val == $selected)
                    $attributes['selected'] = 'selected';

                $s .= self::tag('option', $option, $attributes);
            }
        }

        return $s;
    }

    static public function radioOption($type, $options, $checked, $label_attr, $input_attr)
    {
        $s = '';
        $input_attr['type'] = $type;
        foreach ($options as $val => $text) {
            if ($val == $checked) {
                $class = &$label_attr['class'];
                $class = $class ? $class . ' active' : 'active';

                $input_attr['checked'] = 'checked';
            }

            $input_attr['value'] = $val;
            $input = self::tag('input', null, $input_attr) . $text;

            $s .= self::tag('label', $input, $label_attr);
        }

        return $s;
    }

}