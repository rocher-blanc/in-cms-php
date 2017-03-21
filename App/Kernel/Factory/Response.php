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

        $this->getApp()->flash('__msg', addslashes( json_encode( $msg ) ) );
        $this->getApp()->flash('__result', $result );
        $this->getApp()->redirect( ( $admin ? $this->getApp()->config('admin.url') . '/' : '' ) . ltrim( $url , '/' ) );
        die;
    }

    /* Retour des messages d'erreurs par FLASH (Slim) pour les formulaires classiques ou RQT en GET */
    public function redirect( $url = '' )
    {
        $this->getApp()->redirect( $url == '' ? '/' : $url );
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
		die;
	}
	
	public function redirectForbidden()
	{
		$this->getApp()->redirect( $this->getApp()->config('forbidden.url') ) ;
	}
	
	public function redirectLogin()
	{
		$this->getApp()->redirect( $this->getApp()->config('login.url') ) ;
	}

    public function redirectHome()
    {
        $this->getApp()->redirect( $this->getApp()->config('admin.url') ) ;
    }

    public function show404()
    {
        //$this->getApp()->notFound() ;
    }

    public function error( $message , $type = '404' )
	{
        if ( DEBUG ) throw new \App\Kernel\Exception( $message ) ;
        else         die("Une erreur est survenue lors du chargement de la page") ;
    }
}