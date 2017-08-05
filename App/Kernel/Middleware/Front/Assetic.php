<?php

namespace App\Kernel\Middleware\Front;

class Assetic extends \Slim\Middleware
{
    public function __construct() {}

    public function call()
    {
        $this->next->call();

        $html = $this->app->response->body();
        $this->app->response->body($this->modifyResponse($html));
    }

    private function modifyResponse( $html )
    {
        if ( $this->app->view()->parserExtensions )
        {
            foreach( $this->app->view()->parserExtensions as $key => $ext )
            {
                if ( substr( get_class( $ext ) , -9 ) == 'TwigFront' )
                {
                    $html = str_replace( ASSET_CSS_VAR , $ext->css() , $html ) ;
                    return str_replace( ASSET_JS_VAR , $ext->javascript() , $html ) ;
                }
            }
        }
    }
}