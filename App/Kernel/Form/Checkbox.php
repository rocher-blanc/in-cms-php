<?php

namespace App\Kernel\Form;

class Checkbox
{
	public function html( $field, $name, $value = NULL )
	{
		$this->initLib() ;
		return $this->getMultiselect( $field, $name, $value ) ;
	}
	
	private function getMultiselect( $field, $name, $value = NULL )
	{
		$this->_lib_js  = 'bootstrap-multiselect/dist/js/bootstrap-multiselect.js';
		$this->_lib_css = 'bootstrap-multiselect/dist/css/bootstrap-multiselect.css';
		
		if ( $value === NULL ) $value = array();
		
		$select = '' ;
		foreach( $field->getData('option') as $key => $opt )
		{
			$select .= '<option value="' . $key . '"' . ( in_array( $key , $value ) ? " selected" : '' ) . '>' . $opt . '</option>' . "\n" ;
		}
		
		return '
		<div class="input-group btn-group">
			<span class="input-group-addon">
				<i class="fa fa-th-list"></i>
			</span>
			<select name="' . $name . '" id="id_' . $field->getColumn() . '" data-plugin-multiselect multiple="multiple">
				' . $select . '
			</select>
		</div>' ;
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