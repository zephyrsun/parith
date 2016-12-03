<?php

$db_ip = '127.0.0.1';
$db_port = 3366;
$db_username = 'root';
$db_password = '123456';
$db_name = 'example';

return array(
    'namespace' => 'Example',
    'error_class' => '\Example\Controller\Error',
    'router' => [
        'index' => ['c', 'a'],
        'default' => ['Index', 'index'],
    ],

    'database_1' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_username, 'password' => $db_password, 'dbname' => $db_name],
    'database_2' => ['host' => $db_ip, 'port' => $db_port, 'username' => $db_username, 'password' => $db_password, 'dbname' => $db_name],
);