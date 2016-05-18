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
        if ( array_key_exists( $key , $this->getVar() ) )   return $this->a[ $key ];
        else if ( DEBUG === true )                          return '##' . $key . '##' ;
        else                                                return '' ;
    }

    public function getVar()
    {
        return $this->a;
    }
}