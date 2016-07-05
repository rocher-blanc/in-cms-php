<?php

namespace App\Kernel\Form;

class Document
{
	public function __construct()
	{
		$this->_doc = new \App\Kernel\Back\Document;
	}
	
	protected function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}
	
	private function hasValue()
	{
		if ( $this->value === NULL or $this->value === 0 or $this->value === '0' ) 	return false ;
		else																		return true ;
	}
	
	private function validValue( $mini )
	{
        if ( $this->hasValue() && file_exists( WEB_PATH . $mini ) ) 	return true ;
		else															return false ;
	}
	
	public function html( $field, $name, $value = NULL )
	{
		$this->value = $value ;
		
		$this->_lib_js  = [
			'jquery-file-upload/js/vendor/jquery.ui.widget.js',
			'jquery-file-upload/js/jquery.iframe-transport.js',
			'jquery-file-upload/js/jquery.fileupload.js',
			'jcrop/js/jquery.Jcrop.min.js'
		];
		
		$this->_lib_css = [
			'jquery-file-upload/css/jquery.fileupload.css',
			'jcrop/css/jquery.Jcrop.min.css'
		];
		
		if ( $this->hasValue() ) 
		{
			$this->_doc->setDocumentId( $value );
			$this->_doc->getById();
		}

		$html = '
		<div id="bloc_doc_id_' . $name . '">
			<input type="hidden" name="' . $name . '" id="id_' . $name . '" value="' . ( $this->validValue( $value ) == true ? $value : '' ) . '" />
				<div class="blocDocument" id="doc_source_' . $field->getName() . '">' ;
					if ( $this->hasValue() )
					{
						// on affiche le document
					}
					$html.= '
				</div>
				<a class="btn btn-info fileinput-button openDocument" data-field="' . $field->getName() . '" data-fieldid="id_' . $name . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/document' ) . '">
					<i class="fa fa-plus"></i>
					<span>Sélectionner</span>
				</a>
		</div>' ;
	
		return $html;
	}

	private function initLib()
	{
		$this->_lib_js  = '' ;
		$this->_lib_css = '' ;
	}
	
	public function getLibCss()
	{
		return $this->_lib_css ;
	}
	
	public function getLibJs()
	{
		return $this->_lib_js ;
	}
}