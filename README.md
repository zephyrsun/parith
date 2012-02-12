#Parith

##Introduction

Parith is a lightweight PHP framework. It aims to help you build efficient web applications.
It is licenced under the MIT License so you can use it for any personal or corporate projects free of charge.

##Requirements

* PHP 5.3.3+
* PDO (if using the Database)

##Installation

Your directory structure could like this:

    ├─Parith
    │  ├─Controller
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

###How to use in PHP 5.3.0 to PHP 5.3.2

if you use PHP 5.3.0 to PHP 5.3.2, you must config Config/Router.php like this:

    <?php
        return array('default' => array('controller' => 'Home', 'action' => 'index'));
        // default is: array('default' => array('controller' => 'Index', 'action' => 'index'));
    ?>

config 'Home' as default controller, will prevent PHP treat 'index' as __construct. there is 'Home.php':

    <?php
    namespace Website\Controller;
    
    class Home
    {
        public function index(){}
    }
    ?>

###How to set URL rewrite

Apache:

    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    RewriteRule (.*) index.php?PATH_INFO=$1&%{QUERY_STRING}

Nginx:

    try_files $uri $uri/ /index.php?PATH_INFO=$uri&$query_string;


