<?php

namespace App\Kernel;

class View
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $folder = [] ;

    private static ?self $instance = null;

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    protected function getApp(): SlimBridge
    {
        return SlimBridge::getInstance() ;
    }

    /* ************************************************** */
    /* ****************   SINGLETONE   ****************** */
    /* ************************************************** */

    public static function getInstance(): self
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
        $this->getApp()->appendViewData([ $key => $var ]);
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function getData( $key )
    {
        return $this->getApp()->getViewData( $key );
    }

    /* ************************************************** */
    /* ****************    FUNCTIONS   ****************** */
    /* ************************************************** */

    public function render( $template , $args = [] )
    {
        $twig = $this->getApp()->view();
        if ( $twig === null ) return;

        $env    = $twig->getEnvironment();
        $loader = $env->getLoader();

        // Vérifie si le template existe, sinon essaie avec .html
        $exists = false;
        try {
            $loader->getSourceContext( $template );
            $exists = true;
        } catch ( \Twig\Error\LoaderError $e ) {
            try {
                $loader->getSourceContext( $template . '.html' );
                $template .= '.html';
                $exists    = true;
            } catch ( \Twig\Error\LoaderError $e2 ) {
                // template introuvable
            }
        }

        if ( $exists === false ) return;

        if ( DEBUG_TWIG ?? false )
        {
            $this->getTwigDebugBar()->merge( $args );
            $args['__debug_twig__'] = $this->getTwigDebugBar()->getDebugTwig();
        }

        $globalData = $this->getApp()->getViewData();
        echo $env->render( $template , array_merge( $globalData , $args ) );
    }

    public function getTwigDebugBar()
    {
        return \App\Kernel\Front\TwigDebugBar::getInstance();
    }

    public function fetch( $template , $args = [] )
    {
        $twig = $this->getApp()->view();
        if ( $twig === null ) return '';

        $env        = $twig->getEnvironment();
        $globalData = $this->getApp()->getViewData();
        return $env->render( $template , array_merge( $globalData , $args ) );
    }

    public function appendData( $array )
    {
        if ( DEBUG_TWIG ?? false )
        {
            $this->getTwigDebugBar()->merge( $array );
        }
        $this->getApp()->appendViewData( $array );
    }
}
