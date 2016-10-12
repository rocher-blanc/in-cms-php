<?php

namespace App\Kernel\Middleware\Front;

class Ip extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->next->call();
    }

    public function observe()
    {
        $Ip = new \App\Kernel\Front\Ip;
        $Ip->observe() ;
    }
}