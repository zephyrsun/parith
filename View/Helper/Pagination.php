<?php

/**
 * Helper\Pagination
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith\View\Helper;

class Pagination extends \Parith\Object
{
    public $total = 1, $current = 1, $links = 2, $uri = '', $uri_query = '';

    public $options = array(
        'per_page' => 10,
        'links' => 2,
        'class' => 'pagination',
        'id' => 'pagination',
        'attributes' => array('class' => 'pagination'),
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    );

    /**
     * @param $total
     * @param $current
     * @param string $uri
     *                - Rewrite On: '/index/page'
     *                - Rewrite Off: '' leave it empty
     *
     * @param array $uri_query
     *               - Rewrite On: array('catalog' => 1) will be a part of query string
     *               - Rewrite Off: array('controller' => 'index', 'action' => 'page', 'catalog' => 1)
     *                              controller/action should be passed here
     *
     * @param array $options
     */
    public function __construct($total, $current, $uri = '', array $uri_query = array(), array $options = array())
    {
        $this->options = \Parith\App::getOption('pagination', $options) + $this->options;

        $this->current = $current;
        $this->total = ceil($total / $this->options['per_page']);
        $this->links = $this->options['links'];

        if ($uri) {
            $this->uri = rtrim($uri, '/') . '/';
            if ($uri_query)
                $this->uri_query = '?' . \Parith\Lib\URL::query($uri_query);

        } else {
            $uri_query['page'] = '__page';
            $this->uri_query = '?' . \Parith\Lib\URL::query($uri_query);
        }
    }

    /**
     * @param $page
     * @return string
     */
    public function uri($page)
    {
        if ($this->uri) {
            $uri = $this->uri . $page . $this->uri_query;
        } else {
            $uri = str_replace('__page', $page, $this->uri_query);
        }

        return $uri;
    }

    /**
     * @static
     * @param $page
     * @param $text
     * @param array $attributes
     * @return string
     */
    public static function tag($page, $text, $attributes = array())
    {
        return HTML::tag('li', HTML::link($page, $text), $attributes);
    }

    /**
     * @return string
     */
    public function previous()
    {
        if ($this->current > 1)
            return static::tag($this->uri($this->current - 1), $this->options['prev_text']);

        return static::tag('javascript:;', $this->options['prev_text'], array('class' => 'disabled'));
    }

    /**
     * @return string
     */
    public function next()
    {
        if ($this->total > 1 && $this->current < $this->total)
            return static::tag($this->uri($this->current + 1), $this->options['next_text']);

        return static::tag('javascript:;', $this->options['next_text'], array('class' => 'disabled'));
    }

    /**
     * @return string
     */
    public function first()
    {
        if ($this->current > $this->links + 1) {
            return static::tag($this->uri(1), 1) . static::dots();
        }

        return '';
    }

    public static function dots()
    {
        return static::tag('javascript:;', '...', array('class' => 'disabled'));
    }

    /**
     * @return string
     */
    public function last()
    {
        if ($this->current + $this->links < $this->total)
            return static::dots() . static::tag($this->uri($this->total), $this->total);

        return '';
    }

    /**
     * @return string
     */
    public function generate()
    {
        $start = $this->current - $this->links;
        $start > 0 or $start = 1;

        $end = $this->current + $this->links;
        $end < $this->total or $end = $this->total;

        $html = $this->previous() . $this->first();

        for ($i = $start; $i <= $end; ++$i) {
            // Wrap the link in a list item
            $html .= static::tag($this->uri($i), $i, $this->current == $i ? array('class' => 'active') : array());
        }

        $html .= $this->last() . $this->next();

        return HTML::tag('div', '<ul>' . $html . '</ul>', $this->options['attributes']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->generate();
    }
}