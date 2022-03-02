<?php

namespace App\Kernel;

class View
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $folder = [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {

    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    /* ************************************************** */
    /* ****************   SINGLETONE   ****************** */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new View;
        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */

    public function setFolder( $folder )
    {
        $this->folder[] = $folder ;
    }

    public function setData( $key , $var )
    {
        return $this->getApp()->view()->setData( $key , $var );
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function getData( $key )
    {
        return $this->getApp()->view()->getData( $key );
    }

    /* ************************************************** */
    /* ****************    FUNCTIONS   ****************** */
    /* ************************************************** */

    public function render( $template , $args = [] )
    {
        $exist = false ;
        foreach( CMS::getInstance()->getApp()->view()->twigTemplateDirs as $folder )
        {
            if ( file_exists( $folder . '/' . $template ) )
            {
                $exist = true ;
            }
        }

        if ( $exist === false )
        {
            $template.= '.html' ;
        }

    	if( DEBUG_TWIG )
		{
			$this->getTwigDebugBar()->merge( $args );
			$args['__debug_twig__'] = $this->getTwigDebugBar()->getDebugTwig();
		}
    	$this->getApp()->render( $template , $args );
    }

    public function getTwigDebugBar()
	{
		return \App\Kernel\Front\TwigDebugBar::getInstance();
	}


    public function fetch( $template , $args = [] )
    {
        return $this->getApp()->view()->fetch( $template , $args );
    }

    public function appendData( $array )
    {
		if( DEBUG_TWIG )
		{
			$this->getTwigDebugBar()->merge( $array );
		}
        return $this->getApp()->view()->appendData( $array );
    }
}