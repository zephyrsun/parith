Parith
========

Introduction
------------

Parith is a lightweight PHP framework. It aims to help you build efficient web applications.

Requirements
------------

* PHP 5.3.3+
* PDO (if using the Database)

Installation
------------

Your directory structure could like this:

├─Parith
│  ├─Cache
│  ├─Controller
│  ├─Core
│  ├─DataSource
│  ├─Lib
│  ├─Model
│  ├─View
│  ...
└─YOURAPP
    ├─Config
    │      Router.php
    │
    ├─Controller
    │      Home.php
    │
    ...

if you use PHP 5.3.0 to PHP 5.3.2, you must config Config/Router.php like this:

<?php
    return array('default' => array('controller' => 'Home', 'action' => 'index'));
?>
// default is: array('default' => array('controller' => 'Index', 'action' => 'index'));

config 'Home' as default controller, will prevent PHP treat 'index' as __construct.
