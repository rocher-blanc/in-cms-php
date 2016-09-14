<?php

namespace App\Kernel\Middleware\Front;

class Adwords extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->next->call();
    }

    public function observe()
    {
        if ( isset( $_GET['glcid'] ) )
        {
            $_SESSION['glcid'] = $_GET['glcid'] ;
        }
    }
}