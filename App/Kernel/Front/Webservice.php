<?php

namespace App\Kernel\Front;

class Webservice
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_url = '' ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->loadUrl() ;
        header('Content-Type: application/json');
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    protected function setUrl( $var )
    {
        $this->_url = $var ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getOffset( $num )
    {
        $num++; // on coupe "api"
        if ( $this->Lang()->count() > 1 ) $num++; // on coupe le langage

        return $num ;
    }

    protected function getUrl( $num )
    {
        if ( array_key_exists( $num , $this->_url ) )
        {
            return $this->_url[ $num ] ;
        }
        else
        {
            return NULL ;
        }
    }

    protected function getKeyToken()
    {
        return 'token_api' ;
    }

    /* ************************************************** */
    /* ******************    ISER    ******************** */
    /* ************************************************** */

    protected function isModule()
    {
        if ( $this->getUrl(0) == 'module' && $this->getUrl(1) !== NULL )
        {
            return true ;
        }
        else
        {
            return false ;
        }
    }

    protected function isElementModule()
    {
        $output = preg_replace( '/[^0-9]/', '', $this->getUrl(2) );
        if ( $this->getUrl(2) !== NULL && $output != '' )
        {
            return true ;
        }
        else
        {
            return false ;
        }
    }

    protected function isCustomElement()
    {
        $output = preg_replace( '/[^0-9]/', '', $this->getUrl(2) );
        if ( $this->getUrl(2) !== NULL && $output == '' )
        {
            $ws = $this->Container()->module( $this->getUrl(1) )->getWebservice() ;
            if ( $ws->isDeclare( $_SERVER['REQUEST_METHOD'] , $this->getUrl(2) )
        }
    }

    /* ************************************************** */
    /* *******************   TOOLS   ******************** */
    /* ************************************************** */

    protected function App()
    {
        return \App\Kernel\CMS::getInstance()->getApp() ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

    /* ************************************************** */
    /* *****************    DISPLAY    ****************** */
    /* ************************************************** */

    protected function loadUrl()
    {
        $num = 1; // on coupe "api"
        if ( $this->Lang()->count() > 1 ) $num++; // on coupe le langage

        $this->setUrl( $this->Factory()->Url()->cutUrl( $num ) );
    }

    public function display()
    {
        if ( $this->isSecured() )
        {
            $this->deleteToken();

            if ( $this->isModule() )
            {
                if ( $this->isElementModule() )
                {
                    $this->displayElementModule();
                }
                else if ( $this->isCustomElement() )
                {
                    $this->displayCustomElementModule();
                }
                else
                {
                    $this->displayModule();
                }
            }
        }
        else
        {
            return $this->error( "Security error" , 500 ) ;
        }
    }

    protected function displayModule()
    {
        $ws = $this->Container()->module( $this->getUrl(1) )->getWebservice() ;
        $filter = $this->deleteParams( $_GET ) ;
        $sort  = $this->checkSort( $_GET['sort'] ) ;
        $limit  = $this->checkLimit( $_GET['limit'] ) ;
        $offset = $this->checkOffset( $_GET['offset'] ) ;

        return $ws->displayGetAll( $filter , $sort , $limit , $offset );
    }

    protected function displayElementModule()
    {
        $ws = $this->Container()->module( $this->getUrl(1) )->getWebservice() ;

        return $ws->displayGetOne( $this->getUrl(2) ) ;
    }

    /* ************************************************** */
    /* ******************    FILTERS   ****************** */
    /* ************************************************** */

    protected function deleteParams( $var )
    {
        unset( $var[ $this->getKeyToken() ] );
        unset( $var['sort'] );
        unset( $var['limit'] );
        unset( $var['offset'] );

        return $var ;
    }

    /* ************************************************** */
    /* *****************   SECURITY   ******************* */
    /* ************************************************** */

    protected function checkSort( $var )
    {
        return $var ;
    }

    protected function checkOffset( $var )
    {
        return $var ;
    }

    protected function checkLimit( $var )
    {
        return $var ;
    }

    protected function isSecured()
    {
        return false ; // default
    }

    protected function deleteToken()
    {
        unset( $_GET[ $this->getKeyToken() ] );
    }

    /* ************************************************** */
    /* ******************    ERRORS    ****************** */
    /* ************************************************** */

    protected function error( $msg , $code )
    {
        http_response_code( $code );

        echo json_encode([
            'message' => $msg,
            'code'    => $code
        ]);
        die;
    }
}