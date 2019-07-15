<?php

namespace App\Kernel\Back;

use App\Kernel;
use App\Kernel\Install;
use App\Kernel\Middleware\Back\Auth;
use App\Kernel\Middleware\Back\Guard;
use App\Kernel\Middleware\Back\User;
use App\Kernel\Middleware\CsrfGuard;
use App\Kernel\View\TwigAdmin;
use App\Kernel\View\TwigDebug;
use App\Kernel\View\TwigLang;

class Loader
{
    protected $kernel = NULL ;

    public function __construct()
    {
        $this->kernel = new Kernel();
    }

    protected function getAdminFolder()
    {
        return "/" . Install::getAdminFolder() ;
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

        $this->kernel->setMiddleware(new CsrfGuard( $this->kernel->config('token') ));
        $this->kernel->setMiddleware(new Auth);
        $this->kernel->setMiddleware(new Guard);
        $this->kernel->setMiddleware(new User);

        #########################################################
        /* ****************   Extensions   ******************* */
        #########################################################

        $this->kernel->setParserExtension(new TwigAdmin);
        $this->kernel->setParserExtension(new TwigLang);
        $this->kernel->setParserExtension(new TwigDebug);

        #########################################################
        /* ****************     Plugin     ******************* */
        #########################################################

        $this->kernel->addPlugin(new Router(array_merge([
                CONTROLLERS_PATH,
                PROJECT_EXT_CONTROLLER_PATH
            ],
            $this->getRouterFolder()
        ))) ;
        $this->kernel->addPlugin(new Menu) ;
    }

    public function index( $run = true )
    {

        $this->preload();
        $this->kernel->run( $run );
    }
}