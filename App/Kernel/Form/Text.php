<?php

namespace App\Kernel\Form;

class Text extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
        return $this->View()->fetch( 'form/text.twig' , [
            'name' => $name,
            'value' => htmlspecialchars( $value ),
            'maxlength' => $field->getData('maxLength'),
            'hasFlag' => $field->hasLang(),
            'flag' => $field->getData('flag'),
            'required' => $field->isRequired()
        ]);
	}
}