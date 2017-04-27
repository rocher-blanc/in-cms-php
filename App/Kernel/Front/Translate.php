<?php

namespace App\Kernel\Front;

class Translate
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */

    private static $instance = NULL ;
    private $language = NULL ;

	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct()
    {
        $lang = strtoupper( $this->Lang()->getActive()->url ) ;

        if ( file_exists( LANG_PATH . '/' . $lang . '.php' ) )
        {
            $class = "\Project\Lang\\" . $lang  ;
            $this->language = new $class;
        }
    }

	/* ************************************************** */
	/* ******************   SETTER   ******************** */
	/* ************************************************** */



	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */

    public static function getInstance()
    {
        if ( self::$instance === NULL ) self::$instance = new Translate;
        return self::$instance ;
    }

    private function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    public function getText( $key , $var = [] )
    {
        $str = ( $this->language !== NULL ? nl2br( $this->language->get( $key ) ) : '##' . $key . '##' ) ;

        if ( !empty( $var ) )
        {
            foreach( $var as $key => $value )
            {
                $str = str_replace( '{' . $key . '}' , $value , $str );
            }
        }

        return $str ;
    }

	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */


}