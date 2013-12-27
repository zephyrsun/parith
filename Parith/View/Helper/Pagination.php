<?php

/**
 * Helper\Pagination
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\View\Helper;

use \Parith\Lib\URL as ParithURL;
use \Parith\Result;
use \Parith\App;

class Pagination extends Result
{
    public $options = array(
        'per_page' => 10,
        'links' => 2,
        'class' => 'pagination',
        'id' => 'pagination',
        'attributes' => array('class' => 'pagination'),
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'source' => array(),
        'query' => array(),
    )
    , $total = 1
    , $current = 1
    , $links = 2
    , $url = '';

    /**
     * @param $total
     * @param array|string $query
     *                  - array: array('controller' => 'search', 'action' => 'index', 'catalog' => 1)
     *                  - string: /search /index, 'catalog' passed by $options['query']
     * @param array $options
     */
    public function __construct($total, $query = '', array $options = array())
    {
        $this->options = $options + App::getOption('pagination') + $this->options;

        if ($this->options['source'])
            $source = $this->options['source'];
        else
            $source = $_GET;

        if (isset($source['page']))
            $this->current = $source['page'];
        else
            $this->current = 1;

        $this->total = ceil($total / $this->options['per_page']);
        $this->links = $this->options['links'];

        if (is_array($query)) {

            $query += $this->options['query'] + $source;
            $query['page'] = '__page__';

            $this->url = ParithURL::link('?' . ParithURL::query($query));

        } else {
            $this->url = ParithURL::link($query);

            $query = $this->options['query'] + $source;
            $query['page'] = '__page__';

            //unset($query[0], $query[1]);

            $this->url .= '?' . ParithURL::query($query);
        }
    }

    /**
     * @param $page
     * @return string
     */
    public function link($page)
    {
        return str_replace('__page__', $page, $this->url);
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
        if ($this->current > $this->links + 1) {
            return static::tag($this->link(1), 1) . static::dots();
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
            return static::dots() . static::tag($this->link($this->total), $this->total);

        return '';
    }


    /**
     * @static
     * @param $total
     * @param array|string $query
     * @param array $options
     * @return mixed
     */
    public static function generate($total, $query = '', array $options = array())
    {
        $class = get_called_class();
        $class = new $class($total, $query, $options);

        return $class->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $start = $this->current - $this->links;
        $start > 0 or $start = 1;

        $end = $this->current + $this->links;
        $end < $this->total or $end = $this->total;

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

        return HTML::tag('div', '<ul>' . $html . '</ul>', $this->options['attributes']);
    }
}