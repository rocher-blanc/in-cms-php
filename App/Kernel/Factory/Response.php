<?php

namespace App\Kernel\Factory;

use App\Kernel\Factory;
use App\Kernel\SlimBridge;
use App\Kernel\Exception\RedirectException;

class Response
{
    protected function getApp(): SlimBridge
    {
        return SlimBridge::getInstance();
    }

    public function Factory(): Factory
    {
        return Factory::getInstance();
    }

    /* ------------------------------------------------------------------ */
    /* Flash + Redirect                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Flash un message et redirige.
     * Slim 4 : flash via slim/flash, redirect via RedirectException.
     */
    public function flashAndRedirect( $msg , $result = false , $url = '' , $admin = true )
    {
        if ( $url == '' )
        {
            $urlTab = $this->Factory()->Url()->cutUrl();
            $url    = "module/" . $urlTab[1] ;
        }

        $this->getApp()->flash('__msg', addslashes( $msg ) );
        $this->getApp()->flash('__result', (string)(int)$result );
        $target = ( $admin ? $this->getApp()->config('admin.url') . '/' : '' ) . ltrim( $url , '/' );
        $this->redirect( $target );
    }

    /** Flash seul (sans redirect) */
    public function flash( $msg , $result = false )
    {
        $this->getApp()->flash('__msg', addslashes( $msg ) );
        $this->getApp()->flash('__result', (string)(int)$result );
    }

    /* ------------------------------------------------------------------ */
    /* Output                                                              */
    /* ------------------------------------------------------------------ */

    /** Slim 2 : $app->response->body($msg) */
    public function show( $msg )
    {
        $this->getApp()->response()->body( $msg );
    }

    /* ------------------------------------------------------------------ */
    /* Redirect                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Lève RedirectException — capturée par SlimBridge::wrapCallback.
     * Si appelé hors du pipeline Slim (ex. boot error), utilise header().
     */
    public function redirect( $url = '' , $status = 302 )
    {
        $target = ( $url === '' ? '/' : $url );
        throw new RedirectException( $target , $status );
    }

    /* ------------------------------------------------------------------ */
    /* JSON                                                                */
    /* ------------------------------------------------------------------ */

    public function returnJSON( $msg , $result = false , $extra = [] )
    {
        $this->printJSON( array_merge([
            "msg"    => $msg,
            "result" => $result
        ], $extra ) ) ;
    }

    public function printJSON( $array )
    {
        $this->getApp()->contentType('application/json');
        $this->getApp()->response()->body( json_encode( $array ) );

        if ( array_key_exists( 'msg' , $array ) && array_key_exists( 'result' , $array ) && ( $array['noflash'] ?? false ) != true )
        {
            $this->flash( $array['msg'] , $array['result'] );
        }
    }

    /* ------------------------------------------------------------------ */
    /* Redirections nommées                                                */
    /* ------------------------------------------------------------------ */

    private function saveUrlDestination()
    {
        $url = $this->Factory()->Url()->getFullUrl() ;
        if ( $url != $this->getApp()->config('forbidden.url') ) $_SESSION['url_destination'] = $url ;
    }

    public function redirectUrlDestination()
    {
        if ( ! isset( $_SESSION['url_destination'] ) )
        {
            $this->redirectHome() ;
        }
        else
        {
            $saveUrl = $_SESSION['url_destination'] ;
            unset( $_SESSION['url_destination'] );
            $this->redirect( $saveUrl ) ;
        }
    }

    public function redirectForbidden()
    {
        $this->saveUrlDestination() ;
        $this->redirect( $this->getApp()->config('forbidden.url') ) ;
    }

    public function redirectLogin( $save = true )
    {
        if ( $save ) $this->saveUrlDestination() ;
        $this->redirect( $this->getApp()->config('login.url') ) ;
    }

    public function redirectHome()
    {
        $this->redirect( $this->getApp()->config('admin.url') ) ;
    }

    public function show404()
    {
        $this->getApp()->response()->status( 404 );
        $this->getApp()->render('errors/404.twig.html') ;
    }

    public function error( $message , $type = '404' )
    {
        if ( DEBUG_CMS ) throw new \App\Kernel\Exception( $message ) ;
        else             die("Une erreur est survenue lors du chargement de la page") ;
    }
}
