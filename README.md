#Parith

##Introduction

Parith is a lightweight PHP framework. It aims to help you build efficient web applications.
It is licenced under the MIT License so you can use it for any personal or corporate projects free of charge.

##Requirements

* PHP 5.3.3+
* PDO (if using the Database)

##Installation

Your directory structure could be:

    ├─Parith
    │  ├─Controller
    │  ├─Lib
    │  ├─Model
    │  ├─View
    │  ...
    └─Yourapp
        ├─Config
        │      Router.php
        │
        ├─Controller
        │      Home.php
        │      Error.php
        │
        ...

###How to use in PHP 5.3.0 to PHP 5.3.2

if you use PHP 5.3.0 to PHP 5.3.2, you must config Yourapp/Config/Router.php like this:

    <?php
        return array('default' => array('controller' => 'Home', 'action' => 'index'));
        // default is: array('default' => array('controller' => 'Index', 'action' => 'index'));
    ?>

config 'Home' as default controller, will prevent PHP treat 'index' as __construct. there is 'Home.php':

    <?php
    namespace Yourapp\Controller;
    
    class Home
    {
        public function index()
        {
            ....
        }
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

###How to customize error pages

Below is an example. getText() returns what error messages to be showed by cli().
Web applications can use web() to instead of cli(). You can invoke module \Parith\View to customize it.

    <?php
    namespace Yourapp\Controller;

    class Error extends \Parith\Controller\Error
    {
        public function cli($text = 'error')
        {
            echo PHP_EOL . $text . PHP_EOL;
        }

        public function getText()
        {
            return \Parith\Exception::text($this->exception, '[%s] [%s]');
        }
    }
    ?>

