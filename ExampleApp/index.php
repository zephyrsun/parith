<?php

include \dirname(__DIR__) . '/Parith/App.php';

$config = include __DIR__ . '/Config/ExampleApp.php';

$app = new \Parith\App($config);

$app->run();