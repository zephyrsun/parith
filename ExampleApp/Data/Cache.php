<?php

namespace Jieru\Data;

class Cache extends \Parith\Data\Source\Memcache
{

    public function __construct($options = array())
    {
        $options or $options = \Parith\App::getOption('memcache');

        parent::__construct($options);
    }
}
