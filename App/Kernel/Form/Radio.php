<?php

namespace App\Kernel\Form;

class Radio extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		$this->initLib() ;
		if ( $field->getData('isBoolean') == true ) return $this->getBoolean( $field, $name, $value ) ;
	}
	
	private function getBoolean( $field, $name, $value = NULL )
	{
		$this->_lib_js  = 'cmsmedias/canvas/js/components/bs-switches.js';
		$this->_lib_css = 'cmsmedias/canvas/css/src/components/bs-switches.css';

        return $this->View()->fetch( 'form/radio_boolean.twig.html' , [
            'name' => $name,
            'value' => $value
        ]);
    }
}