<?php

/*
 * Usage:
 * php cli.php 'Index/index?get1=foo&get2=bar' 'post1=1&post2=2'
 */

require \dirname(__DIR__) . '/Src/Bootstrap.php';

$config = require __DIR__ . '/Config/ExampleApp.php';
\Parith\App::setOption($config);

\Parith\App::cli(__DIR__);