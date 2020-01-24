<?php

namespace App\Kernel\Middleware;

class SessionCrypt extends \Slim\Middleware\SessionCookie
{
    protected function getKey()
    {
        return substr( md5( base64_encode( $this->app->request()->getUrl() ) ) , 0 , 24 ) ;
    }

    /**
     * Load session
     */
    protected function loadSession()
    {
        if ( session_id() === '' ) session_start() ;
    }

    /**
     * Save session
     */
    protected function saveSession()
    {

    }
}