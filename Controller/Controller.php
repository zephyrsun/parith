<?php

/**
 * Controller
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

namespace Parith;

abstract class Controller
{
    /**
     * @param $name
     * @param $arguments
     * @throws \Parith\Exception
     */
    public function __call($name, $arguments)
    {
        throw new \Parith\Exception('Action "' . $name . '" not found', 404);
    }
}