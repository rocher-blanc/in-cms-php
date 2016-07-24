<?php

namespace App\Kernel\Back;

class Loader
{
    protected $kernel = NULL ;

    protected function preload()
    {
        #########################################################
        /* ************* General Configuration *************** */
        #########################################################

        $admin = "/" . \App\Kernel\Install::getAdminFolder() ;

        $this->kernel = new \App\Kernel([
            'admin.url' 	=> $admin,
            'token' 		=> 'csrf_token',
            'config' 		=> 'back',
            'session' 		=> 'auth_user',
            'session_name' 	=> 'Admin_' . md5( $_SERVER['SERVER_NAME'] ),
            'auth_username' => 'username',
            'auth_password' => 'password',
            'login.url' 	=> $admin . '/secured/login',
            'logout.url' 	=> $admin . '/secured/logout',
            'forbidden.url' => $admin . '/secured/forbidden'
        ]);

        #########################################################
        /* ****************   Middleware   ******************* */
        #########################################################

        $this->kernel->setMiddleware(new \App\Kernel\Middleware\CsrfGuard( $this->kernel->config('token') ));
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Back\Auth);
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Back\Guard);

        #########################################################
        /* ****************   Extensions   ******************* */
        #########################################################

        $this->kernel->setParserExtension(new \App\Kernel\View\TwigAdmin);

        #########################################################
        /* ****************     Plugin     ******************* */
        #########################################################

        $this->kernel->addPlugin(new \App\Kernel\Back\Router([
            CONTROLLERS_PATH
        ])) ;
        $this->kernel->addPlugin(new \App\Kernel\Back\Menu) ;
        $this->kernel->addPlugin(new \App\Kernel\Back\Theme) ;
    }

    public function index()
    {
        $this->preload();
        $this->kernel->run();
    }
}