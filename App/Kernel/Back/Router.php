<?php

namespace App\Kernel\Back;

class Router
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	/*
	 * @array
	 * Contient toute la decoupe de l'URL
	 */
	private $_url = [] ;

    /*
     * @array
     * Contient tous les dossiers à aller checker
     */
	private $folders = [] ;

	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct( $folders = [] )
    {
        $this->folders = $folders ;
    }
	
	/* ************************************************** */
	/* ******************   SETTER   ******************** */
	/* ************************************************** */
	
	private function setUrl( $var )
	{
		return $this->_url[] = $var ;
	}
	
	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */
	
	private function getApp()
	{
		return \Slim\Slim::getInstance() ;
	}
	
	private function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}
	
	public function getUrl()
	{
		return $this->_url ;
	}
	
	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */
	
	private function cutUrl()
	{
		$this->_url = $this->Factory()->Url()->cutUrl() ;
	}
	
	public function load()
	{
		$this->cutUrl() ;
		
		$app 	= $this->getApp() ;
		$urlTab = $this->getUrl() ;
			
		if ( empty( $urlTab ) )
		{
			// INDEX
            $file = '' ;
            foreach( $this->folders as $folder )
            {
                if ( file_exists( $folder . '/index.php' ) ) $file = $folder . '/index.php' ;
            }

            if ( ! empty( $file ) ) require $file ;
		}
		else
		{
			switch( $urlTab[0] )
			{
				case "module" :
                    $folders = $this->folders ;
					$app->group('/' . $urlTab[0] , function () use ( $app , $urlTab , $folders )
					{
                        $file = '' ;
                        foreach( $folders as $folder )
                        {
                            if ( file_exists( $folder . '/' . $urlTab[0] . '/index.php' ) ) $file = $folder . '/' . $urlTab[0] . '/index.php' ;
                        }

                        if ( ! empty( $file ) ) require $file ;
					});
				break;
				case "ext" :
				case "admin" :
                    $folders = $this->folders ;

                    $app->group('/' . $urlTab[0] , function () use ( $app , $urlTab , $folders )
                    {
                        $file = '' ;
                        foreach( $folders as $folder )
                        {
                            if ( file_exists( $folder . '/' . $urlTab[0] . '/' . $urlTab[1] . '.php' ) ) $file = $folder . '/' . $urlTab[0] . '/' . $urlTab[1] . '.php' ;
                        }

                        if ( ! empty( $file ) ) require $file ;
					});
				break;
				default :
                    $file = '' ;
                    foreach( $this->folders as $folder )
                    {
                        if ( file_exists( $folder . '/' . $urlTab[0] . '/' . $urlTab[1] . '.php' ) )
                        {
                            $file = $folder . '/' . $urlTab[0] . '/' . $urlTab[1] . '.php' ;
                            $group = true ;
                        }
                        else if ( file_exists( $folder . '/' . $urlTab[0] . '.php' ) )
                        {
                            $file = $folder . '/' . $urlTab[0] . '.php' ;
                            $group = false ;
                        }
                    }

                    if ( ! empty( $file ) )
                    {
                        if ( $group )
                        {
                            $app->group('/' . $urlTab[0] , function () use ( $app , $urlTab , $file )
                            {
                                require $file ;
                            });
                        }
                        else
                        {
                            require $file ;
                        }
                    }
				break;
			}
			
			// Erreur 404
			$app->notFound(function () use($app) {
				$app->render('errors/404.twig.html') ;
			});
		}
	}
}