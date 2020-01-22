<?php

namespace App\Kernel\Front;

abstract class LanguageModel
{
    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function get( $key )
    {
        $key = strtolower( $key );

        if ( array_key_exists( $key , $this->getVar() ) )   return html_entity_decode( $this->a[ $key ] );
        else if ( DEBUG_CMS === true )                      return '##' . $key . '##' ;
        else                                                return '' ;
    }

    public function exist( $key )
    {
        $key = strtolower( $key );

        return array_key_exists( $key , $this->getVar() );
    }

    public function getVar()
    {
        return $this->a;
    }
}