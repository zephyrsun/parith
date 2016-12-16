#Parith

##Introduction

Parith is a compact PHP framework. It aims to help you build efficient web applications.
It is licenced under the MIT License so you can use it for any personal or corporate projects free of charge.

##Requirements

* PHP 7+
* PDO (if using the Database)

##Installation

Your directory structure could be:

    ├─Parith
    │  ├─Controller
    │  ├─Lib
    │  ├─Model
    │  ├─View
    │  ...
    └─App
       ├─Config
       │      Router.php
       │
       ├─Controller
       │      Index.php
       │
       ...

###Code
'namespace' => 'Example',
'error_class' => '\Example\Controller\Error',
'router' => array(
    'index' => array('c', 'a'),
    'default' => array('Index', 'index'),
),
index.php:

	$config = array(
        'namespace' => 'Example',
        'error_class' => '\Example\Controller\Error',
        'router' => array(
            'index' => array('c', 'a'),
            'default' => array('Index', 'index'),
        ),
    );
    $app = new \Parith\App($config);
    $app->run();

Controller/Index.php:

    <?php
    namespace App\Controller;

    class Home
    {
        public function index()
        {
            ....
        }
    }

###How to set URL rewrite

Nginx:

    location / {
        try_files $uri $uri/ /?URI=$uri&$args;
    }
