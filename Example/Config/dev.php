<?php

$db_ip = '10.0.0.250';
$db_port = 3306;
$db_username = 'root';
$db_password = 'test';
$db_name = 'imrobotic_business';

return array(
    'namespace' => 'Example',
    'error_class' => '\Example\Controller\Error',
    'route' => ['Controller', 'Index', 'index'],

    'database_1' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_username, 'password' => $db_password, 'dbname' => $db_name],
    'database_2' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_username, 'password' => $db_password, 'dbname' => $db_name],
);