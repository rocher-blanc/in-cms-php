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
            if ( $this->app->request->post('moduleAction') == '1' )
            {
                $this->user()->login();
            }
        }
        else
        {
            $this->user()->connectWithFacebook() ;

            if ( $this->app->request->get('logout') == 'me' )
            {
                $this->user()->logout();
            }

            if ( $this->app->request->get('user_validation') == 'me' )
            {
                $this->user()->validation();
            }
        }

        $this->user()->appendVar();
    }
}