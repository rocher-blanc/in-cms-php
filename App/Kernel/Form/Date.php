<?php

namespace App\Kernel\Form;

class Date extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		$this->_lib_js  = [
            'cmsmedias/canvas/js/components/datepicker.js',
            'cmsmedias/canvas/js/components/datepicker.fr.js'
        ];
		$this->_lib_css = 'cmsmedias/canvas/css/src/components/datepicker.css';

        return $this->View()->fetch( 'form/date.twig' , [
            'name' => $name,
            'value' => $value,
            'column' => $field->getColumn(),
            'required' => $field->isRequired()
        ]);
	}
}