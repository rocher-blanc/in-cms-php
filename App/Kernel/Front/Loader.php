<?php

namespace App\Kernel\Front;

class Loader
{
    protected $kernel = NULL ;

    public function __construct()
    {
        $this->kernel = new \App\Kernel();
    }

    protected function preload()
    {
        #########################################################
        /* ************* General Configuration *************** */
        #########################################################

        $this->kernel->config([
            'session' 		=> 'auth_user',
            'session_name' 	=> 'Front_' . md5( $_SERVER['SERVER_NAME'] )
        ]);
        $this->kernel->load();
        $this->kernel->activeDbCaching() ;

        #########################################################
        /* ****************   Middleware   ******************* */
        #########################################################

        $files = glob( MIDDLEWARE_PROJECT_PATH . '/*.php' );

        if ( $files && count( $files ) > 0 )
        {
            foreach( $files as $file )
            {
                $class = str_replace( _PATH_ , '' , $file );
                $class = str_replace( ".php" , '' , $class );
                $class = str_replace( "/" , '\\' , $class );

                $this->kernel->setMiddleware(new $class);
            }
        }

        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Front\Assetic);
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Front\User);
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Front\Adwords);
        $this->kernel->setMiddleware(new \App\Kernel\Middleware\Front\Referer);

        #########################################################
        /* ****************   Extensions   ******************* */
        #########################################################

        $this->kernel->setParserExtension(new \App\Kernel\View\TwigFront);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigModule);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigUrl);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigHelper);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigMenu);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigLang);
        $this->kernel->setParserExtension(new \App\Kernel\View\TwigDebug);

        #########################################################
        /* ****************     Plugin     ******************* */
        #########################################################

        $this->kernel->addPlugin(new \App\Kernel\Front\Router) ;
        $this->kernel->addPlugin(new \App\Kernel\Front\Language) ;
        $this->kernel->addPlugin(new \App\Kernel\Front\Meta) ;
    }

    public function index()
    {
        $this->preload();
        $this->kernel->run();
    }
}