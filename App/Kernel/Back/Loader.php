<?php

namespace App\Kernel\Back;

class Loader
{
    protected $kernel = NULL ;

    public function __construct()
    {
        $this->kernel = new \App\Kernel();
    }

    protected function getAdminFolder()
    {
        return "/" . \App\Kernel\Install::getAdminFolder() ;
    }

    protected function getRouterFolder()
    {
        return [] ;
    }

    protected function preload()
    {
        #########################################################
        /* ************* General Configuration *************** */
        #########################################################

        $admin = $this->getAdminFolder() ;

        $this->kernel->config([
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
        $this->kernel->load();

        #########################################################
        /* ****************   Middleware   ******************* */
        #########################################################

        $this->kernel->setMiddleware(new \App\Kernel\Middleware\CsrfGuard( $this->kernel->config('token') ));
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Back\Auth);
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Back\Guard);
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Back\User);

        #########################################################
        /* ****************   Extensions   ******************* */
        #########################################################

        $this->kernel->setParserExtension(new \App\Kernel\View\TwigAdmin);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigLang);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigDebug);

        #########################################################
        /* ****************     Plugin     ******************* */
        #########################################################

        $this->kernel->addPlugin(new \App\Kernel\Back\Router(array_merge([
            CONTROLLERS_PATH,
            PROJECT_EXT_CONTROLLER_PATH
        ], $this->getRouterFolder()))) ;
        $this->kernel->addPlugin(new \App\Kernel\Back\Menu) ;
    }

    public function index( $run = true )
    {
        $this->preload();
        $this->kernel->run( $run );
    }
}