<?php

namespace App\Kernel\Middleware\Front;

class Referer extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->app->hook('slim.before', [$this, 'observe']);
        $this->next->call();
    }

    public function observe()
    {
        if ( isset( $_SERVER['HTTP_REFERER'] ) )
        {
            if ( strpos( $_SERVER['HTTP_REFERER'] , $_SERVER['HTTP_HOST'] ) === false )
            {
                $_SESSION['referer'] = $_SERVER['HTTP_REFERER'] ;
            }
        }
    }
}