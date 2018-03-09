<?php

namespace App\Kernel;

class CMS
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->view()->appendData([
            'debug' => $this->isDev()
        ]);
    }

    /* ************************************************** */
    /* ****************      ISER     ******************* */
    /* ************************************************** */

    public function isDev()
    {
        return DEBUG ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new CMS;
        return self::$instance ;
    }

    public function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    /* ************************************************** */
    /* ****************     REQUEST   ******************* */
    /* ************************************************** */

    public function request()
    {
        return $this->getApp()->request() ;
    }

    public function response()
    {
        return $this->getApp()->response ;
    }

    public function getIp()
    {
        return $_SERVER['REMOTE_ADDR'] ;
    }

    /* ************************************************** */
    /* ****************      VIEW     ******************* */
    /* ************************************************** */

    public function view()
    {
        return new View;
    }

    public function fetch( $tpl , $arg = [] )
    {
        return $this->view()->fetch( $tpl , $arg ) ;
    }

    public function render( $tpl , $arg = [] )
    {
        if ( isset( $_GET['noview'] ) or isset( $_POST['noview'] ) )    return "" ;
        else                                                            return $this->view()->render( $tpl , $arg ) ;
    }
}