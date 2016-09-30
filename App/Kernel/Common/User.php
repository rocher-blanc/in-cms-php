<?php

namespace App\Kernel\Common;

class User
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected static $instance = NULL ;

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function getNewToken()
    {
        $length = 32 ;
        $uniq   = false ;

        while( $uniq == false )
        {
            if ( function_exists('mcrypt_create_iv') )
            {
                $token = bin2hex(mcrypt_create_iv( $length , MCRYPT_DEV_URANDOM ) );
            }
            else if ( function_exists('random_bytes') )
            {
                $token = bin2hex( random_bytes( $length ) );
            }
            else if ( function_exists('openssl_random_pseudo_bytes') )
            {
                $token = bin2hex( openssl_random_pseudo_bytes( $length ) );
            }

            $uniq = $this->uniqToken( $token ) ;
        }

        return $token ;
    }

    protected function uniqToken( $token )
    {
        $ct = \DB::for_table('user_front')
            ->where_equal('user_front_token', $token )
            ->count();

        if ( $ct == 0 ) return true ;
        else            return false ;
    }
}