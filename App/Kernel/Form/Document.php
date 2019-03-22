<?php

namespace App\Kernel\Form;

use App\Kernel\Http;

class Document extends \App\Kernel\Back\Form
{
	public function __construct()
	{
		$this->_doc = new \App\Kernel\Back\Document;
	}
	
	private function hasValue()
	{
		if ( $this->value === NULL or $this->value === 0 or $this->value === '0' ) 	return false ;
		else																		return true ;
	}
	
	private function validValue( $id )
	{
        if ( $this->hasValue()  ) 	return true ;
		else						return false ;
	}
	
	public function html( $field, $name, $value = NULL )
	{
		$this->value = $value ;

		$tab = [];
		
		if ( $this->hasValue() ) 
		{
			$exp = explode( ',' , $value );

			if ( $exp )
			{
				foreach( $exp as $row )
				{
					$this->_doc->setDocumentId( $row );
					$this->_doc->getNameById();
					$ico = $this->_doc->getIcon( $this->_doc->getDocumentName() );

					$tab[ $row ] = [
						'url'   => Http::getInstance()->getUrl() . str_replace( WEB_PATH , '' , DOCUMENT_PATH ) . "/" . $this->getFolder() . "/" . $this->_doc->getDocumentName(),
						'name'  => $this->_doc->getDocumentName(),
						'title' => $this->_doc->getAltText(),
						'id'    => $row,
						'ico'   => $ico
					];
				}
			}

		}

		return $this->View()->fetch( 'form/document.twig' , [
			'name' => $name,
			'docs' => $tab,
			'field_name' => $field->getName()
		]);
	}
}