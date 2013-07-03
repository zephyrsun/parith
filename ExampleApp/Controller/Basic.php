<?php

namespace ExampleApp\Controller;
use ExampleApp\Lib;

class Basic extends \Parith\Basic
{
    public function __construct()
    {
        echo '<p>Welcome, I am Basic::__construct().</p>';
        $this->beforeAction();
    }

    public function beforeAction()
    {
        echo '<p>Hello, I am Basic::beforeAction().Remove Me, never mind.</p>';
    }

    public function __destruct()
    {
        $this->afterAction();
    }

    public function afterAction()
    {
        echo '<p>Hello, I am Basic::afterAction().Remove Me,too.</p>';
    }
}