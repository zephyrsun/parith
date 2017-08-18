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


class Paginator extends \Parith\Result
{
    public $options = [
        'size' => 10,
        'range' => 5,
        'attributes' => ['class' => 'pagination'],
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ];

    private
        $_total = 1,
        $_page_num = 1,
        $_current = 1,
        $_uri = '';

    /**
     * Paginator constructor.
     * @param $total
     * @param $size
     */
    public function __construct($total, $size = 0)
    {
        $this->setOptions(\Parith::getEnv('paginator'));

        if ($size > 0)
            $this->options['size'] = $size;

        $this->_current = &$_GET['page'] or $this->_current = 1;

        $this->_total = $total;

        $this->_page_num = ceil($this->_total / $size);
    }

    public function total()
    {
        return $this->_total;
    }

    public function pageNum()
    {
        return $this->_page_num;
    }

    public function size()
    {
        return $this->options['size'];
    }

    public function currentPage()
    {
        return $this->_current;
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
            return $this->tag($this->link($this->_current - 1), $this->options['prev_text'], ['class' => 'page-prev']);

        return '';
    }

    /**
     * @param $start
     * @return string
     */
    public function first($start)
    {
        if ($start > 1)
            return $this->tag($this->link(1), 1, ['class' => 'page-first']) . $this->dots();

        return '';
    }

    /**
     * @param $end
     * @return string
     */
    public function lastItem($end)
    {
        if ($this->_page_num > $end)
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
            return $this->tag($this->link($this->_current + 1), $this->options['next_text'], ['class' => 'page-next']);

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

    /**
     * @param bool $domain
     *          true: "http://yourdomain/?page=1"
     *          false: "?page=1"
     *
     * @return string
     */
    public function render($domain = true)
    {
        //uri
        $this->_uri = $domain ? \Parith\Lib\URI::uri(null, ['page' => '__PAGE__']) : '?page=__PAGE__';

        $range = $this->options['range'];

        $pt = $this->_page_num;

        $mid = floor($range / 2);

        $end = $this->_current + $mid;
        if ($end > $pt)
            $end = $pt;

        $start = $end - $range + 1;
        if ($start < 1) {
            $start = 1;
            $end = min($range, $pt);
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

    /**
     * @return string
     */
    public function __toString()
    {
        $this->render();
    }
}