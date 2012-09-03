<?php

/**
 * Bootstrap
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

\define('PARITH_DIR', __DIR__ . DIRECTORY_SEPARATOR);

// load core class
require PARITH_DIR . 'Core/Common.php';
require PARITH_DIR . 'Core/Monitor.php';
require PARITH_DIR . 'Core/Result.php';
require PARITH_DIR . 'Core/App.php';
require PARITH_DIR . 'Controller/Controller.php';