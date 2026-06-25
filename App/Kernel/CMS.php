<?php

namespace App\Kernel;

use App\Kernel\Front\Translate;
use App\Kernel\SlimBridge;
use App\Kernel\SlimResponse;

class CMS
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private static $instance = NULL ;
    private $config = [];

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        /*$this->view()->appendData([
            'debug' => $this->isDev()
        ]);*/
    }

    /* ************************************************** */
    /* ****************      ISER     ******************* */
    /* ************************************************** */

    public function isDev()
    {
        return DEBUG_CMS ;
    }

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */

    public function setConfig( array $config )
    {
        $this->config = $config ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new CMS;
        return self::$instance ;
    }

    public function getConfig()
    {
        return $this->config ;
    }

    public function getApp(): SlimBridge
    {
        return SlimBridge::getInstance();
    }

    /* ************************************************** */
    /* ****************     REQUEST   ******************* */
    /* ************************************************** */

    public function request()
    {
        return $this->getApp()->request() ;
    }

    public function response(): SlimResponse
    {
        return $this->getApp()->response();
    }

    public function config( $key )
    {
        if ( array_key_exists( $key , $this->getConfig() ) )
        {
            return $this->config[ $key ] ;
        }
        else
        {
            return NULL ;
        }
    }

    public function getIp()
    {
        if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
        {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) )
        {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        else
        {
            return $_SERVER['REMOTE_ADDR'] ;
        }
    }

    /* ************************************************** */
    /* ****************   TRANSALTE   ******************* */
    /* ************************************************** */

    public function text( $key )
    {
        return Translate::getInstance()->getText( 'key' );
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
        if ( isset( $_GET['noview'] ) or isset( $_POST['noview'] ) or $this->getApp()->response()->getBuffer() !== '' ) return "" ;
        else                                                                                                                                                       return $this->view()->render( $tpl , $arg ) ;
    }
}