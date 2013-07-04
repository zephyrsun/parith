<?php
namespace ExampleApp\Data;

class Example extends CachedModel
{
    public $db_name = 'example_app', $table_name = 'example', $primary_key = 'id';
}
