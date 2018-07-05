<?php

namespace App\Kernel\Back;

class User extends \App\Kernel\Common\User
{

    /* ************************************************** */
    /* ****************     ISER      ******************* */
    /* ************************************************** */

    public function isLogged()
    {
        return false;
    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL )
        {
            self::$instance = new User;
        }

        return self::$instance ;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */



    protected function returnError( $key , $result = false )
    {
        if ( $result == false ) $this->_error = true ;

        if ( $this->isAjax() )
        {
            $this->Factory()->Response()->returnJSON( $this->text( $key ) , $result );
        }
        else
        {
            return $result ;
        }
    }
}