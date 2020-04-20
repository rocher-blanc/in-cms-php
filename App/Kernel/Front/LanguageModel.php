<?php

namespace App\Kernel\Front;

use App\Kernel\Factory;

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

        if ( array_key_exists( $key , $this->getVar() ) )
        {
            return html_entity_decode( $this->a[ $key ] );
        }
        else if( empty( trim( $key ) ) )
        {
            return "";
        }
        else if( DEBUG_CMS === true )
        {
            $this->addKey( $key , "##$key##" );
            return '##' . $key . '##' ;
        }
        else
        {
            return '' ;
        }
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

    public function addKey( $key , $value )
    {
        $this->a[ $key ] = $value ;
        $this->saveFile();
    }

    protected function saveFile()
    {
        $split = explode("\\" , get_class($this) );
        $fileName = strtoupper( end($split) );

        $src = "<"."?php\n";
        $src.= "namespace Project\Lang;\n";
        $src.= "class " .  $fileName . " extends \App\Kernel\Front\LanguageModel {\n";
        $src.= "\tprotected $"."a = [\n";

        foreach( $this->a as $cle => $value )
        {
            $txt = str_replace('"' , '\"' , trim( $value ) );
            $src.= "\t\t\"" . trim( $cle ) . "\" => \"" . $txt . "\",\n";
        }

        $src.= "\t];\n";
        $src.= "}\n";

        Factory::getInstance()->File()->create( LANG_PATH . "/" . $fileName . ".php" , $src );
    }
}