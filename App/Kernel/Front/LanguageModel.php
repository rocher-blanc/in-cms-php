<?php

namespace App\Kernel\Front;

use App\Kernel\Factory;

abstract class LanguageModel
{
	private static $autoAddingExcluded = [
		'content',
		'email',
		'on_line',
        "disable_modif",
"dispatch_date",
"email_gabarit",
"error_message",
"group_name",
"grp_destinataire",
"key",
"mandatory_dispatch_date",
"mandatory_email",
"mandatory_email_address",
"mandatory_gabarit",
"mandatory_group_name",
"mandatory_grp_destinataire",
"mandatory_key",
"mandatory_label",
"mandatory_model_name",
"mandatory_name",
"mandatory_newsletter_name",
"mandatory_newsletter_type",
"mandatory_parent_newsletter",
"mandatory_response_email",
"mandatory_sender_email",
"mandatory_sender_sing",
"mandatory_sender_sing_name",
"mandatory_subject",
"mandatory_subject_name",
"mandatory_template_name",
"mandatory_title",
"mandatory_txt",
"newsletter_name",
"nom",
"normal",
"not_uniq_email",
"oui",
"parent_newsletter",
"renvoi_email_nonlu",
"renvoi_nonclic",
"response_email",
"sender_email",
"sender_name",
"sender_sing",
"status",
"subject",
"text",
"title",
"type",
"variables_environnement",
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