<?php

/**
 * Helper\Pagination
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\View\Helper;

use \Parith\Lib\URI;

class Pagination
{
    public $options = array(
        'page_size' => 10,
        'nav_num' => 5,
        'class' => 'pagination',
        'id' => 'pagination',
        'attributes' => array('class' => 'pagination'),
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'query' => array(),
    )
    , $total = 1
    , $current = 1
    , $uri = '';

    /**
     * @param $total
     * @param array $options
     */
    public function __construct($total, array $options = array())
    {
        $this->options = $options += \Parith\App::getOption('pagination') + $this->options;

        $this->current = $_GET['page'] ?? 1;

        $this->total = ceil($total / $options['page_size']);

        //$this->size = $options['size'];

        $uri = URI::link();

        $this->uri = preg_replace('/page=\d+/', 'page=__PAGE__', $uri, 1, $n);
        if (!$n)
            $this->uri .= (strpos($uri, '?') > -1 ? '&' : '?') . 'page=__PAGE__';
    }

    /**
     * @param $page
     *
     * @return string
     */
    public function link($page)
    {
        return str_replace('__PAGE__', $page, $this->uri);
    }

    /**
     * @static
     *
     * @param       $page
     * @param       $text
     * @param array $attributes
     *
     * @return string
     */
    static public function tag($page, $text, $attributes = array())
    {
        return HTML::tag('li', HTML::link($page, $text), $attributes);
    }

    /**
     * @return string
     */
    public function previous()
    {
        if ($this->current > 1)
            return static::tag($this->link($this->current - 1), $this->options['prev_text']);

        return '';
        //return static::tag('javascript:;', $this->options['prev_text'], array('class' => 'disabled'));
    }

    /**
     * @param $start
     * @return string
     */
    public function first($start)
    {
        if ($start > 1)
            return static::tag($this->link(1), 1) . $this->dots();

        return '';
    }

    /**
     * @param $end
     * @return string
     */
    public function last($end)
    {
        if ($this->total > $end)
            return $this->dots();

        return '';
    }

    /**
     * @param $end
     * @return string
     */
    public function next($end)
    {
        if ($end > $this->current)
            return static::tag($this->link($this->current + 1), $this->options['next_text']);

        return '';
    }

    public function dots()
    {
        return static::tag('javascript:;', '&hellip;', array('class' => 'disabled'));
    }

    /**
     * @param $total
     * @param array $options
     * @return string
     */
    static public function generate($total, array $options = array())
    {
        $class = get_called_class();
        $obj = new $class($total, $options);

        return $obj->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $size = $this->options['nav_num'];

        $mid = floor($size / 2);

        $end = $this->current + $mid;
        if ($end > $this->total)
            $end = $this->total;

        $start = $end - $size + 1;
        if ($start < 1) {
            $start = 1;
            $end = min($size, $this->total);
        }

        $html = $this->previous() . $this->first($start);

        for ($i = $start; $i <= $end; ++$i) {

            if ($this->current == $i) {
                $attr = array('class' => 'active');
            } else {
                $attr = array();
            }

            $html .= static::tag($this->link($i), $i, $attr);
        }

        $html .= $this->last($end) . $this->next($end);

        return HTML::tag('ul', $html, $this->options['attributes']);
    }
}