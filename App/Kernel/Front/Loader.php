<?php

namespace App\Kernel\Front;

class Loader
{
    public function index()
    {
        #########################################################
        /* ************* General Configuration *************** */
        #########################################################

        $kernel = new \App\Kernel([
            'session' 		=> 'auth_user',
            'session_name' 	=> 'Front_' . md5( $_SERVER['SERVER_NAME'] )
        ]);

        $kernel->activeDbCaching() ;

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

                $kernel->setMiddleware(new $class);
            }
        }

        $kernel->setMiddleware(new \App\Kernel\Middleware\Front\User);

        #########################################################
        /* ****************   Extensions   ******************* */
        #########################################################

        $kernel->setParserExtension(new \App\Kernel\View\TwigFront);
        $kernel->setParserExtension(new \App\Kernel\View\TwigUrl);
        $kernel->setParserExtension(new \App\Kernel\View\TwigHelper);
        $kernel->setParserExtension(new \App\Kernel\View\TwigMenu);
        $kernel->setParserExtension(new \App\Kernel\View\TwigLang);
        $kernel->setParserExtension(new \App\Kernel\View\TwigDebug);

        #########################################################
        /* ****************     Plugin     ******************* */
        #########################################################

        $kernel->addPlugin(new \App\Kernel\Front\Router) ;
        $kernel->addPlugin(new \App\Kernel\Front\Language) ;
        $kernel->addPlugin(new \App\Kernel\Front\Meta) ;

        $kernel->run();
    }
}