<?php

/*
 * Usage:
 * php cli.php 'Index/index?get1=foo&get2=bar' 'post1=1&post2=2'
 */

include \dirname(__DIR__) . '/Parith/App.php';

\Parith\App::registerAutoloader();

$config = include __DIR__ . '/Config/ExampleApp.php';

$app = new \Parith\App($config);

$app->cli();