<?php

/**
 * Helper\Pagination
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\View\Helper;

use \Parith\Lib\URI;

class Pagination extends \Parith\Result
{
    public $options = array(
        'per_page' => 10,
        'link_num' => 3,
        'class' => 'pagination',
        'id' => 'pagination',
        'attributes' => array('class' => 'pagination'),
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'query' => array(),
    )
    , $total = 1
    , $current = 1
    , $link_num = 2
    , $uri = '';

    /**
     * @param $total
     * @param array $options
     */
    public function __construct($total, array $options = array())
    {
        $this->options = $options += \Parith\App::getOption('pagination') + $this->options;

        $this->current = $_GET['page'] ?? 1;

        $this->total = ceil($total / $options['per_page']);
        $this->link_num = $options['link_num'];

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

        return static::tag('javascript:;', $this->options['prev_text'], array('class' => 'disabled'));
    }

    /**
     * @return string
     */
    public function next()
    {
        if ($this->total > 1 && $this->current < $this->total)
            return static::tag($this->link($this->current + 1), $this->options['next_text']);

        return static::tag('javascript:;', $this->options['next_text'], array('class' => 'disabled'));
    }

    /**
     * @return string
     */
    public function first()
    {
        if ($this->current > $this->link_num + 1) {
            return static::tag($this->link(1), 1) . $this->dots();
        }

        return '';
    }

    public function dots()
    {
        return static::tag('javascript:;', '...', array('class' => 'disabled'));
    }

    /**
     * @return string
     */
    public function last()
    {
        if ($this->current + $this->link_num < $this->total)
            //return $this->dots() . static::tag($this->link($this->total), $this->total);
            return $this->dots();

        return '';
    }


    /**
     * @param $total
     * @param array $query
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
        $start = $this->current - $this->link_num;

        if ($start <= 0)
            $start = 1;

        $end = $this->current + $this->link_num;
        if ($end >= $this->total)
            $end = $this->total;

        $html = $this->previous() . $this->first();

        for ($i = $start; $i <= $end; ++$i) {

            if ($this->current == $i) {
                $attributes = array('class' => 'active');
                $page = 'javascript:;';
            } else {
                $attributes = array();
                $page = $this->link($i);
            }

            $html .= static::tag($page, $i, $attributes);
        }

        $html .= $this->last() . $this->next();

        return HTML::tag('ul', $html, $this->options['attributes']);
    }
}