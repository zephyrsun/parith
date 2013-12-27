<?php

//error_reporting(E_ALL);

include \dirname(__DIR__) . '/Parith/App.php';

\Parith\App::registerAutoloader();

$config = include __DIR__ . '/Config/ExampleApp.php';

$app = new \Parith\App($config);

$app->run();