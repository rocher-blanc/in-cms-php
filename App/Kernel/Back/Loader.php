<?php

namespace App\Kernel\Back;

class Loader
{
    public function index()
    {
        #########################################################
        /* ************* General Configuration *************** */
        #########################################################

        $admin = "/" . \App\Kernel\Install::getAdminFolder() ;

        $kernel = new \App\Kernel([
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

        $kernel->setMiddleware(new \App\Kernel\Middleware\CsrfGuard( $kernel->config('token') ));
        $kernel->setMiddleware(new \App\Kernel\Middleware\Back\Auth);
        $kernel->setMiddleware(new \App\Kernel\Middleware\Back\Guard);

        #########################################################
        /* ****************   Extensions   ******************* */
        #########################################################

        $kernel->setParserExtension(new \App\Kernel\View\TwigAdmin);

        #########################################################
        /* ****************     Plugin     ******************* */
        #########################################################

        $kernel->addPlugin(new \App\Kernel\Back\Router([
            CONTROLLERS_PATH
        ])) ;
        $kernel->addPlugin(new \App\Kernel\Back\Menu) ;
        $kernel->addPlugin(new \App\Kernel\Back\Theme) ;

        $kernel->run();
    }
}