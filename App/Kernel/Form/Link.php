<?php

namespace App\Kernel\Form;

class Link extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
        return $this->View()->fetch( 'form/link.twig.html' , [
            'name' => $name,
            'value' => $value,
            'maxlength' => $field->getData('maxLength'),
            'required' => $field->isRequired()
        ]);
	}
}