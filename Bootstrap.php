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

\define('PARITH_DIR', \dirname(__FILE__) . DIRECTORY_SEPARATOR);

// load core class
require __DIR__ . '/Core/Common.php';
require __DIR__ . '/Core/Monitor.php';
require __DIR__ . '/Core/Result.php';
require __DIR__ . '/Core/App.php';
require __DIR__ . '/Controller/Controller.php';