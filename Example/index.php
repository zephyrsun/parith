<?php

include \dirname(__DIR__) . '/Parith/App.php';

$config = include __DIR__ . '/Config/dev.php';

$app = new \Parith\App($config);

$app->run();