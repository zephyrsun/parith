<?php

include '../Parith/Parith.php';

$config = include __DIR__ . '/Config/dev.php';

(new \Parith($config))->run();