<?php
/**
 * Created by IntelliJ IDEA.
 * User: sunzhenghua
 * Date: 15/5/31
 * Time: 下午3:24
 */

namespace Example\Controller;

class Error extends Basic
{
    public function render(\Throwable $e, string $str)
    {
        echo "<pre>$str</pre>";
    }
} 