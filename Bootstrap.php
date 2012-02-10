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

\define('DS', \DIRECTORY_SEPARATOR);
\define('PARITH_DIR', \dirname(__FILE__) . DS);

# load core class
require 'Core/Common.php';
require 'Core/Monitor.php';
require 'Core/Result.php';
require 'Core/App.php';