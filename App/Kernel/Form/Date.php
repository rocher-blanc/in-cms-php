<?php

namespace App\Kernel\Form;

class Date extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		$this->_lib_js  = [
            'bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
            'bootstrap-datepicker/dist/locales/bootstrap-datepicker.fr.min.js'
        ];
		$this->_lib_css = 'bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css';

        return $this->View()->fetch( 'form/date.twig.html' , [
            'name' => $name,
            'value' => $value,
            'column' => $field->getColumn(),
            'required' => $field->isRequired()
        ]);
	}
}