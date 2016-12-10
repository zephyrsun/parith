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
    public function renderError(\Throwable $e)
    {
        $error = $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine() . PHP_EOL;
        $error .= $e->getTraceAsString();

        Response::error("<pre>$error</pre>", 0);
    }
} 