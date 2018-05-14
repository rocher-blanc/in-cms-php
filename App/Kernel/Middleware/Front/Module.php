<?php

namespace App\Kernel\Middleware\Front;

class Module extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->app->hook('slim.before', [$this, 'observe']);
        $this->next->call();
    }

    public function observe()
    {
        if ( $this->app->request->isPost() )
        {
            if ( $this->app->request->post('moduleAction') == '1' && $this->app->request->post('moduleName') != '' && $this->app->request->post('keyControl') != '' )
            {
                $Controller = \App\Kernel\Container::getInstance()->module( $this->app->request->post('moduleName') )->getController();
                $Controller->listenForm();
            }
        }
    }
}