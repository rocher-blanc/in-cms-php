<?php

namespace App\Kernel\Middleware\Back;

class User extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        if ( ACTIVE_USER ) $this->app->hook('slim.before', [$this, 'observe']);
        $this->next->call();
    }

    private function user()
    {
        return \App\Kernel\Back\User::getInstance();
    }

    public function observe()
    {
        if ( $this->app->request->isPost() )
        {
            if ( $this->app->request->post('user_action') == 'update' )
            {
                $this->user()->update();
            }
            else if ( $this->app->request->post('user_action') == 'register' )
            {
                $this->user()->register();
            }
        }
    }
}