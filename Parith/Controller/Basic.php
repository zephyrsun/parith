<?php

/**
 * Controller
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Controller;

abstract class Basic
{
    /**
     * @param $name
     * @param $args
     *
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        throw new \Exception('Action "' . $name . '" not found');
    }
}