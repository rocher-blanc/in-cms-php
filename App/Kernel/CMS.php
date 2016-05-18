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
        return $this->request()->getIp() ;
    }

    /* ************************************************** */
    /* ****************      VIEW     ******************* */
    /* ************************************************** */

    public function view()
    {
        return $this->getApp()->view() ;
    }

    public function fetch( $tpl , $arg = [] )
    {
        return $this->view()->fetch( $tpl , $arg ) ;
    }

    public function render( $tpl , $arg = [] )
    {
        return $this->getApp()->render( $tpl , $arg ) ;
    }
}