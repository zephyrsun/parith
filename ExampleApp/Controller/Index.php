<?php

namespace ExampleApp\Controller;
use ExampleApp\Lib;

class Index extends Basic
{
    public function index()
    {
        echo '<p>Hello, I am Index::index().</p>';

        $view_lib = \Parith\View\Template::factory();
        //$view_lib = new \Parith\View\Template();
        $view_lib->resultSet(array(
            'view_hello'=>'<p>I am just a parameter for View\Template</p>'
        ));
        $view_lib->render('body');
    }

}