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
        /*$viewArray[] = VIEW_PROJECT_PATH ;
        $viewArray[] = VIEW_PROJECT_COMMON_PATH ;

        if ( ! empty( $this->templateFolder ) )
        {
            foreach( $this->templateFolder as $row )
            {
                $viewArray[] = $row ;
            }
        }

        $viewArray[] = TEMPLATES_PATH ;
        $viewArray[] = TEMPLATES_COMMON_PATH ;

        $this->_slim->view()->twigTemplateDirs = $viewArray ;
        $this->_slim->view()->parserExtensions = [
            new \Twig_Extensions_Extension_Text(),
            new \Slim\Views\TwigExtension(),
        ];

        
        $loader = new \Twig_Loader_Filesystem('/path/to/templates');
        $twig = new \Twig_Environment($loader, [
            'debug' => $this->_slim->config('twig.debug'),
            'cache' => $this->_slim->config('cache'),
            'autoescape' => false
        ]);*/
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
        $this->getApp()->render( $template , $args );
    }

    public function fetch( $template , $args = [] )
    {
        return $this->getApp()->view()->fetch( $template , $args );
    }

    public function appendData( $array )
    {
        return $this->getApp()->view()->appendData( $array );
    }
}