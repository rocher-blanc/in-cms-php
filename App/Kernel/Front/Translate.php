<?php

namespace App\Kernel\Front;

use App\Kernel\Back\User;
use App\Kernel\CMS;
use App\Kernel\Exception;
use App\Kernel\Factory;
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
        $type = CMS::getInstance()->config('config') ;

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
            $userId = $_SESSION[ CMS::getInstance()->config('session') ]['id'] ;

            if ( !empty( $userId ) )
            {
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
                    else
                    {
                        $lang = \DB::for_table('lang')
                            ->where_equal('lang_id', $user->user_lang_id)
                            ->find_one();

                        if ( $lang )
                        {
                            if ( $lang->lang_back == 0 )
                            {
                                $user->user_lang_id = 1 ;
                                $user->save();
                            }
                        }
                        else
                        {
                            $user->user_lang_id = 1 ;
                            $user->save();
                        }
                    }
                }
                else
                {
                    throw new Exception("No user found" ) ;
                }

                $lang_id = $user->user_lang_id ;
            }
            else
            {
                $lang_id = 2 ;
            }

            $lang = \DB::for_table('lang')
                ->where_equal('lang_id', $lang_id )
                ->find_one();

            $lang = strtoupper( $lang->lang_url ) ;

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
        $str = $this->language !== NULL
			? nl2br( $this->language->get( $key ) )
			: "##$key##" ;

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

	public static function getLangFilePath( $locale )
	{
		return LANG_PATH . "/" . strtoupper( $locale ) . ".php" ;
	}

	public static function getLangClassName( $locale )
	{
		return "\\Project\\Lang\\" . strtoupper( $locale ) ;
	}

	public static function getLangClassInstance( $locale )
	{
		$className = self::getLangClassName( $locale );
		return new $className ;
	}

	public static function getTranslations( $locale )
	{
		$filename = self::getLangFilePath( $locale );

		if ( file_exists( $filename ) )
		{
			$class = self::getLangClassInstance( $locale );
			return $class->getVar();
		}
		else
		{
			return [];
		}
	}

	public static function generateLangFile( $locale , $translations = [] )
	{
		ksort( $translations );

		$src  = "<"."?php\n";
		$src .= "namespace Project\Lang;\n";
		$src .= "class " . strtoupper( $locale ) . " extends \App\Kernel\Front\LanguageModel {\n";
		$src .= "\tprotected $"."a = [\n";
		foreach( $translations as $key => $value )
		{
			$value = trim( $value );
			$value = str_replace( '"', '\"', $value );
			if ( ! empty( $key ) )
			{
				$src .= "\t\t\"" . trim( $key ) . "\" => \"" . $value . "\",\n";
			}
		}
		$src .= "\t];\n";
		$src .= "}\n";

		$filename = self::getLangFilePath( $locale );
		return Factory::getInstance()->File()->create( $filename , $src );
	}

}