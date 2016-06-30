<?php
/**
 * Created by IntelliJ IDEA.
 * User: sunzhenghua
 * Date: 15/5/31
 * Time: 下午3:24
 */

namespace Example\Controller;

use \Example\Response;

class Error extends Basic
{
    public function __call($val, $args)
    {
        Response::error('Query error', 402);
    }
} 