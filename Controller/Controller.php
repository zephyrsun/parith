<?php

/**
 * Controller
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith;

abstract class Controller
{
    /**
     * @param $name
     * @param $args
     * @return bool
     * @throws \Parith\Exception
     */
    public function __call($name, $args)
    {
        throw new \Parith\Exception('Action "' . $name . '" not found', 404);

        return false;
    }
}