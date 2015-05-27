<?php

namespace ExampleApp\Controller;

use \ExampleApp\Lib;
use \Parith\Controller\Basic as ParithController;
use \Parith\View\Template;

class Basic extends ParithController
{
    protected $input, $output = array(), $format = 'html';

    public function __construct()
    {
        $this->input = file_get_contents('php://input');

        $format = & $_GET['format'];
        if ($format)
            $this->format = $format;
    }

    public function __destruct()
    {
        $this->{$this->format}();

        //close links
        \Parith\Data\Source\Database::closeAll();
    }

    protected function json()
    {
        echo json_encode($this->output);
    }

    protected function html()
    {
        //$view_lib = \Parith\View\Template::factory();
        $view_lib = new Template();
        $view_lib->resultSet($this->output);
        $view_lib->render('body');
    }
}