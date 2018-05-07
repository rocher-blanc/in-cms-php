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
        return $this->View()->fetch( 'form/radio_boolean.twig' , [
            'name' => $name,
            'value' => $value
        ]);
    }
}