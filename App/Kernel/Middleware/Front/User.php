<?php

namespace App\Kernel\Middleware\Front;

class User extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        if ( ACTIVE_USER ) $this->app->hook('slim.before', array($this, 'observe'));
        $this->next->call();
    }

    private function user()
    {
        return \App\Kernel\Front\User::getInstance();
    }

    public function observe()
    {
        $this->user()->observe();

        if ( $this->app->request->isPost() )
        {
            if ( $this->app->request->post('user_action') == 'login' )
            {
                $this->user()->login();
            }

            if ( $this->app->request->post('user_action') == 'lost_password' )
            {
                $this->user()->lostPassword();
            }

            if ( $this->app->request->post('user_action') == 'update' )
            {
                $this->user()->update();
            }

            if ( $this->app->request->post('user_action') == 'register' )
            {
                $this->user()->register();
            }
        }
        else
        {
            $rst = $this->user()->connectWithFacebook() ;
            dump( 'retour fonction' );
            dump( $rst );

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