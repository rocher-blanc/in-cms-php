<?php

namespace App\Kernel\Form;

class Radio extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		if ( $field->getData('isBoolean') == true )
        {
            $this->_lib_js    = 'cmsmedias/canvas/js/components/bs-switches.js';
            $this->_lib_css[] = 'cmsmedias/canvas/css/src/components/bs-switches.css';

            return $this->getBoolean( $field, $name, $value ) ;
        }
	}
	
	private function getBoolean( $field, $name, $value = NULL )
	{
        return $this->View()->fetch( 'form/radio_boolean.twig' , [
            'name' => $name,
            'value' => $value
        ]);
    }
}