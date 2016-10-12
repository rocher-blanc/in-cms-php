<?php

namespace App\Kernel\Middleware\Front;

class Ip extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->app->hook('slim.before', array($this, 'observe'));
        $this->next->call();
    }

    public function observe()
    {
        $Ip = new \App\Kernel\Front\Ip;
        $Ip->observe() ;
    }
}