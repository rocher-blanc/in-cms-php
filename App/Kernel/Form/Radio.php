<?php

namespace App\Kernel\Form;

class Radio
{
	public function html( $field, $name, $value = NULL )
	{
		$this->initLib() ;
		if ( $field->getData('isBoolean') == true ) return $this->getBoolean( $field, $name, $value ) ;
	}
	
	private function getBoolean( $field, $name, $value = NULL )
	{
		$this->_lib_js  = 'switchery/dist/switchery.min.js';
		$this->_lib_css = 'switchery/dist/switchery.min.css';
		
		return '
		<input type="hidden" name="' . $name . '" value="0" />
		<input type="checkbox" value="1"' . ( $value == 1 ? ' checked="checked"' : '' ) . ' data-plugin-ios-switch name="' . $name . '" id="id_' . $name . '">' ;
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