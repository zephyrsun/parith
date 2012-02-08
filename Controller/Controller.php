<?php

/**
 * Controller
 * 
 * Parith :: a compact PHP framework
 * 
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2011 Zephyr Sun
 * @license http://www.parith.org/license
 * @version 0.3
 * @link http://www.parith.org/
 */

namespace Parith\Controller;

abstract class Controller
{
    /**
     * @return \Parith\Controller\Controller
     */
    public function __construct()
    {
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        throw new \Parith\Exception('Action "' . $name . '" not found', 404);
    }
}