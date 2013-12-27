<?php

namespace ExampleApp\Data;

use\Parith\Data\Source\Memcache as MC;

use \Parith\App;

class Cache extends MC
{
    public function __construct($options = array())
    {
        $options or $options = App::getOption('memcache');

        $this->link = MC::connection($options);

        //parent::__construct($options);
    }
}
