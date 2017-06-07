<?php

namespace App\Kernel\Middleware\Front;

class Assetic extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->next->call();
        $this->app->hook('slim.before', [$this, 'observe']);
    }

    public function observe()
    {

    }
}