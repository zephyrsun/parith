<?php

namespace ExampleApp\Controller;

class Index extends Basic
{
    public function index()
    {
        $this->output['welcome_msg'] = 'Welcome to Parith Framework!';
    }
}