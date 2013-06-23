<?php

//error_reporting(E_ALL);

require \dirname(__DIR__) . '/Src/Bootstrap.php';

$config = require __DIR__ . '/Config/ExampleApp.php';
\Parith\App::setOption($config);

\Parith\App::run(__DIR__);