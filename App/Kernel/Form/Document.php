<?php

namespace App\Kernel\Form;

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

		$this->_lib_js  = [
			'cmsmedias/canvas/js/components/bs-filestyle.js',
			'cmsmedias/canvas/js/easydoor/locales/fileinput/fr.js',
		];
		$this->_lib_css = ['cmsmedias/canvas/css/src/components/bs-filestyle.css'];
		
		if ( $this->hasValue() ) 
		{
			$this->_doc->setDocumentId( $value );
            $this->_doc->getNameById();
		}

		for( $i=1; $i<=5; $i++)
		{
			$ico = $this->_doc->getIcon( "truc$i.pdf" );
			$tab[$i] = [
				'name' => "truc$i.pdf",
				'ico' => $ico,
			];
		}

		return $this->View()->fetch( 'form/document.twig' , [
			'name' => $name,
			'docs' => $tab,
			'field_name' => $field->getName()
		]);
/*

		$html = '
		<div id="bloc_doc_id_' . $name . '" data-nodoc="' . \App\Kernel\Message::getInstance()->get("no_document") . '">
			<input type="hidden" name="' . $name . '" id="id_' . $name . '" value="' . ( $this->validValue( $value ) == true ? $value : '' ) . '" data-value="' . $value . '" /> 
				<div class="blocDocument" id="doc_source_' . $field->getName() . '">' ;
					if ( $this->hasValue() )
					{
						// on affiche le document
                        $ico = $this->_doc->getIcon( $this->_doc->getDocumentName() );
                        $html.= '<i class="fa fa-file' . $ico->class . '-o" style="color: ' . $ico->color . '"></i> ' ;
                        $html.= '<span>' . $this->_doc->getDocumentName() . '</span>' ;
					}
					else
                    {
                        $html.= \App\Kernel\Message::getInstance()->get("no_document") ;
                    }

        $html.= '
				</div>
				<a class="btn btn-info fileinput-button openDocument" data-field="' . $field->getName() . '" data-fieldid="id_' . $name . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/document' ) . '">
					<i class="fa fa-plus"></i>
					<span>Sélectionner</span>
				</a>
		</div>' ;
	
		return $html;*/
	}
}