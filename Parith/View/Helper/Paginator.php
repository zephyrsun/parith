<?php

/**
 * Helper\Pagination
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

use \Parith\Lib\URI;
use Parith\Result;

class Paginator extends Result
{
    public $options = [
        'per_page' => 10,
        'range' => 5,
        'class' => 'pagination',
        'id' => 'pagination',
        'attributes' => ['class' => 'pagination'],
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ];

    private
        $_total = 1,
        $_page_total = 1,
        $_current = 1,
        $_uri = '';

    /**
     * @param $total
     */
    public function __construct($total)
    {
        $this->options = \Parith\App::getOption('paginator') + $this->options;

        $this->_current = &$_GET['page'] or $this->_current = 1;

        $this->_total = $total;
    }

    public function getTotal()
    {
        return $this->_total;
    }

    public function getPageTotal()
    {
        return $this->_page_total;
    }

    public function all()
    {
        return $this->resultGet();
    }

    /**
     * @param $page
     *
     * @return string
     */
    public function link($page)
    {
        return str_replace('__PAGE__', $page, $this->_uri);
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
    public function tag($page, $text, $attributes = [])
    {
        return HTML::tag('li', HTML::link($page, $text), $attributes);
    }

    /**
     * @return string
     */
    public function previous()
    {
        if ($this->_current > 1)
            return $this->tag($this->link($this->_current - 1), $this->options['prev_text']);

        return '';
    }

    /**
     * @param $start
     * @return string
     */
    public function first($start)
    {
        if ($start > 1)
            return $this->tag($this->link(1), 1) . $this->dots();

        return '';
    }

    /**
     * @param $end
     * @return string
     */
    public function lastItem($end)
    {
        if ($this->_page_total > $end)
            return $this->dots();

        return '';
    }

    /**
     * @param $end
     * @return string
     */
    public function nextItem($end)
    {
        if ($end > $this->_current)
            return $this->tag($this->link($this->_current + 1), $this->options['next_text']);

        return '';
    }

    public function dots()
    {
        return $this->tag('javascript:;', '&hellip;', ['class' => 'disabled']);
    }

    /**
     * @param $total
     * @param array $options
     * @return string
     */
    static public function generate($total, array $options = [])
    {
        $obj = new static($total, $options);

        return $obj->__toString();
    }

    public function render()
    {
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $uri = URI::uri();

        //uri
        $this->_uri = preg_replace('/page=\d+/', 'page=__PAGE__', $uri, 1, $n);
        if (!$n)
            $this->_uri .= (strpos($uri, '?') > -1 ? '&' : '?') . 'page=__PAGE__';

        $range = $this->options['range'];

        $total_page = $this->_page_total = ceil($this->_total / $this->options['per_page']);

        $mid = floor($range / 2);

        $end = $this->_current + $mid;
        if ($end > $total_page)
            $end = $total_page;

        $start = $end - $range + 1;
        if ($start < 1) {
            $start = 1;
            $end = min($range, $total_page);
        }

        $html = $this->previous() . $this->first($start);

        for ($i = $start; $i <= $end; ++$i) {

            if ($this->_current == $i) {
                $attr = ['class' => 'active'];
            } else {
                $attr = [];
            }

            $html .= $this->tag($this->link($i), $i, $attr);
        }

        $html .= $this->lastItem($end) . $this->nextItem($end);

        return HTML::tag('ul', $html, $this->options['attributes']);
    }
}