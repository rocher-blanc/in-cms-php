<?php

namespace App\Kernel\Form;

class Hour extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
        $this->_lib_js  = [
            'cmsmedias/canvas/js/components/datepicker.js',
            'cmsmedias/canvas/js/components/datepicker.fr.js'
        ];

        $this->_lib_js[] = 'cmsmedias/canvas/js/components/timepicker.js';
        $this->_lib_css[] = 'cmsmedias/canvas/css/src/components/timepicker.css';
        $this->_lib_css[] = 'cmsmedias/canvas/css/src/components/datepicker.css';

        return $this->View()->fetch( 'form/hour.twig' , [
            'name' => $name,
            'value' => $value,
            'hour' => $field->getData('hour'),
            'column' => $field->getColumn(),
            'required' => $field->isRequired()
        ]);
	}
}