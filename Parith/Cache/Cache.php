<?php

/**
 * Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Cache;

use \Parith\Result;

class Cache extends Result
{
    public $options = [];

    public function __construct()
    {
        $this->setOptions(\Parith::getOption('cache'));
    }
}