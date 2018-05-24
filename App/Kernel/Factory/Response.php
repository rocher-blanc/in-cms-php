<?php

namespace App\Kernel\Factory;

class Response
{
    public function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    public function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    /* Retour des messages d'erreurs par FLASH (Slim) pour les formulaires classiques ou RQT en GET */
    public function flashAndRedirect( $msg , $result = false , $url = '' , $admin = true )
    {
        if ( $url == '' )
        {
            $urlTab = $this->Factory()->Url()->cutUrl();
            $url = "module/" . $urlTab[1] ;
        }

        $this->getApp()->flash('__msg', addslashes( $msg ) );
        $this->getApp()->flash('__result', $result );
        $this->getApp()->redirect( ( $admin ? $this->getApp()->config('admin.url') . '/' : '' ) . ltrim( $url , '/' ) );
        die;
    }

    /* Retour des messages d'erreurs par FLASH (Slim) pour les formulaires classiques ou RQT en GET */
    public function flash( $msg , $result = false )
    {
        $this->getApp()->flash('__msg', addslashes( $msg ) );
        $this->getApp()->flash('__result', $result );
    }

    /* Retour des messages d'erreurs par FLASH (Slim) pour les formulaires classiques ou RQT en GET */
    public function redirect( $url = '' , $status = 302 )
    {
        $this->getApp()->redirect( ( $url == '' ? '/' : $url ) , $status );
        die;
    }
	
	/* Retour des messages d'erreurs par JSON pour les RQT en AJAX */
	public function returnJSON( $msg , $result = false , $extra = [] )
	{
		$this->printJSON( array_merge([
		    "msg" => $msg,
            "result" => $result
        ], $extra ) ) ;
	}
	
	/* Parse en JSON */
	public function printJSON( $array )
	{
		$this->getApp()->contentType('application/json');
		echo json_encode( $array ) ;

		if ( array_key_exists( 'msg' , $array ) && array_key_exists( 'result' , $array ) )
        {
            $this->flash( $array['msg'] , $array['result'] );
        }
	}

	private function saveUrlDestination()
    {
        $url = $this->Factory()->Url()->getFullUrl() ;
        if ( $url != $this->getApp()->config('forbidden.url') ) $_SESSION['url_destination'] = $url ;
        // if ( ! isset( $_SESSION['url_destination'] ) ) $_SESSION['url_destination'] = $this->Factory()->Url()->getFullUrl() ;
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
            $this->getApp()->redirect( $saveUrl ) ;
        }
    }

    public function redirectForbidden()
    {
        $this->saveUrlDestination() ;
        $this->getApp()->redirect( $this->getApp()->config('forbidden.url') ) ;
    }
	
	public function redirectLogin( $save = true )
	{
        if ( $save ) $this->saveUrlDestination() ;
        $this->getApp()->redirect( $this->getApp()->config('login.url') ) ;
	}

    public function redirectHome()
    {
        $this->getApp()->redirect( $this->getApp()->config('admin.url') ) ;
    }

    public function show404()
    {
        // erreur 404
        if ( file_exists( TEMPLATES_PATH . '/errors/404.twig' ) )
        {
            $app = $this->getApp() ;
            $app->notFound(function () use ($app) {
                $app->render('errors/404.twig') ;
            });
        }
        else
        {
            return false ;
        }
    }

    public function error( $message , $type = '404' )
	{
        if ( DEBUG ) throw new \App\Kernel\Exception( $message ) ;
        else         die("Une erreur est survenue lors du chargement de la page") ;
    }
}