<?php

/*
 * Usage:
 * php cli.php 'Index/index?get1=foo&get2=bar' 'post1=1&post2=2'
 */

include \dirname(__DIR__) . '/Parith/App.php';

$config = include __DIR__ . '/Config/ExampleApp.php';

\Parith\App::registerAutoloader();

$app = new \Parith\App($config);

$app->cli();