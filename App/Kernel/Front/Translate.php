<?php

namespace App\Kernel\Front;

use App\Kernel\CMS;
use App\Kernel\Lang;

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
        $type = CMS::getInstance()->getApp()->config('config') ;

        if ( $type == 'front' )
        {
            $lang = strtoupper( $this->Lang()->getActive()->url ) ;
            if ( file_exists( LANG_PATH . '/' . $lang . '.php' ) )
            {
                $class = "\Project\Lang\\" . $lang  ;
                $this->language = new $class;
            }
        }
        else
        {
            $userId = $_SESSION[ CMS::getInstance()->getApp()->config('session') ]['id'] ;
            $user = \DB::for_table('user')
                ->where_equal('user_id', $userId)
                ->find_one();

            if ( $user )
            {
                if ( $user->user_lang_id == '' )
                {
                    $user->user_lang_id = 1 ;
                    $user->save();
                }
            }

            $lang = strtoupper( $this->Lang()->get( $user->user_lang_id )->url ) ;
            if ( file_exists( LANG_PATH . '/BO' . $lang . '.php' ) )
            {
                $class = "\Project\Lang\BO" . $lang  ;
                $this->language = new $class;
            }
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
        return Lang::getInstance() ;
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

    public function exist( $key )
    {
        if ( $this->language !== NULL )
        {
            return $this->language->exist( $key ) ;
        }

        return false ;
    }

	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */


}