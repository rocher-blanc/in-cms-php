<?php

namespace App\Kernel\Form;

class Number extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		return $this->View()->fetch( 'form/number.twig' , [
			'name' => $name,
			'value' => htmlspecialchars( $value ),
			'step' => $field->getData('step'),
			'hasFlag' => $field->hasLang(),
			'flag' => $field->getData('flag'),
			'required' => $field->isRequired(),
			'unit' => $field->getUnit()
		]);
	}
}