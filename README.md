#Parith (name comes from PHP and arithmetic)

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
    └─App
        ├─Config
        │      Router.php
        │
        ├─Controller
        │      Home.php
        │      Error.php
        │
        ...

###How to use from PHP 5.3.0 to PHP 5.3.2

if you use PHP below 5.3.3, can reference here:

    \Parith\App::setOption('router', array('values' => array('Home', 'index')));

    \Parith\App::run(__DIR__ . '/App');

Here is the controller 'Home.php':

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

Apache:

    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-l

    RewriteRule (.*) index.php/$1?%{QUERY_STRING} [L]

Nginx:

    location / {
        try_files $uri $uri/ /index.php$uri?$args;
    }

    location ~ ^.+\.php {
        fastcgi_pass unix:/tmp/php-fpm.sock; # depends on your machine
        fastcgi_index index.php;

        fastcgi_split_path_info ^((?U).+\.php)(/?.+)$;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        include fastcgi_params;
    }

###How to customize error pages

Below is an example. getText() returns what error messages to be showed by cli().
Web applications can use web() to instead of cli(). You can invoke module \Parith\View to customize it.

    <?php
    namespace App\Controller;

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

