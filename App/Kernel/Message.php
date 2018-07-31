<?php

namespace App\Kernel;

class Message
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private static $instance = NULL ;
	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct() {}
	
	/* ************************************************** */
	/* ****************     GETTER    ******************* */
	/* ************************************************** */

	public static function getInstance()
	{
		if ( self::$instance === NULL ) self::$instance = new Message;
		
		return self::$instance ;
	}
	
	public function get( $key )
	{
		if ( empty( $this->_msg ) ) $this->arrayMessages() ;
		
		if ( ! isset( $this->_msg[ $key ] ) )	return 'No message for this key "' . $key . '"' ;
		else									return $this->_msg[ $key ] ;
	}
	
	public function arrayMessages()
	{
		$this->_msg = array(
			/* MODULE */
			'have_no_content' 	=> "Aucun élément n'est disponible",
			"token_is_bad"		=> "Le token de sécurité est invalide",
			"add_success"		=> "L'élément a bien été ajouté",
			"edit_success"		=> "L'élément a bien été modifié",
			"enable_success"	=> "L'élément a bien été activé",
			"enable_failed"		=> "Une erreur est survenue lors de l'opération",
			"disable_success"	=> "L'élément a bien été désactivé",
			"disable_failed"	=> "Une erreur est survenue lors de l'opération",
			"delete_success"	=> "L'élément a bien été supprimé",
			"delete_failed"		=> "Une erreur est survenue lors de l'opération",
			"order_success"		=> "La position de l'élément a bien été modifié",
			"order_failed"		=> "Une erreur est survenue lors de l'opération",
			
			/* MEDIA */
			"crop_image"						=> "L'image a bien été recadrée",
			"deletemedia_success"				=> "L'image a bien été supprimée",
			"deletemedia_failed"				=> "Une erreur est survenue lors de la suppression de l'image",
			"deletemedia_delete_is_impossible"	=> "L'image ne peut pas être supprimée car elle est encore utilisée",
			"deletemedia_media_not_found"		=> "L'image n'existe plus dans la base de données",
			"deletemedia_no_ressource"			=> "Il n'y a plus aucune image en base de données",

            /* DOCUMENT */
            "no_document"                           => "Aucun document n'est actuellement sélectionné",
            "deletedocument_success"                => "Le document a bien été supprimé",
            "deletedocument_failed"                 => "Une erreur est survenue lors de la suppression du document",
            "deletedocument_delete_is_impossible"   => "Le document ne peut pas être supprimé car elle est encore utilisé",
            "deletedocument_media_not_found"        => "Le document n'existe plus dans la base de données",
            "deletedocument_no_ressource"           => "Il n'y a plus aucun document en base de données",

			/* METADATA */
			"metadata_success" 	=> "Les métadonnées ont bien été mis à jour",

            /* PERFORMACE */
            "performance_success" => "Le serveur CDN a bien été mis à jour",

            /* MAINTENANCE */
            "maintenance_success" => "La configuration du site a bien été mis à jour",

            /* SECURITE */
            "security_success" => "La sécurité de l'administration a bien été mis à jour",

            /* JGALLERY */
            "crop_image_gallery" => "L'image a bien été redimensionnee",

            /* MICRODATA */
            "microdata_success" => "Les microdatas ont bien été mis à jour",

            /* RGPD */
            "rgpd_success" 		=> "Les informations concernant la RGPD ont bien été mise à jour",
		) ;
	}
}