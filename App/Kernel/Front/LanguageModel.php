<?php

namespace App\Kernel\Front;

use App\Kernel\Factory;

abstract class LanguageModel
{
	private static $autoAddingExcluded = [
		'content',
		'email',
		'on_line'
	];

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function get( $key )
    {
        $key = strtolower( trim( $key ) );

        // Si la clé est présente dans les tranductions
        if ( array_key_exists( $key , $this->getVar() ) )
        {
            return html_entity_decode( $this->a[ $key ] );
        }
        // Sinon, si la clé est vide
        else if( empty( $key ) )
        {
            return "";
        }
        // Sinon
        else
        {
        	// Si la clé n'est pas dans les la liste des clés à ignorer, on l'ajoute dans le fichier de traductions
			if( $this->canAutoAddKey($key) )
			{
				$this->addKey( $key , "##$key##" );
			}
        	return DEBUG_CMS === true
				? '##' . $key . '##'
				: '' ;
        }
    }

    private function canAutoAddKey( $key )
	{
		if( self::$autoAddingExcluded == NULL )
		{
			self::$autoAddingExcluded = ( new \Project\Lang\BOFR() )->a;
		}
		return ! in_array( $key , self::$autoAddingExcluded );
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