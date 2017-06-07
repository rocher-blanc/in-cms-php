<?php

namespace App\Kernel\Middleware\Front;

class Adwords extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->app->hook('slim.before', [$this, 'observe']);
        $this->next->call();
    }

    public function observe()
    {
        if ( isset( $_GET['gclid'] ) )
        {
            $_SESSION['gclid'] = $_GET['gclid'] ;
        }
    }
}