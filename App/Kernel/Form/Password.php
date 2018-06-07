<?php

namespace App\Kernel\Form;

class Password extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
        return $this->View()->fetch( 'form/password.twig' , [
            'name' => $name,
            'required' => $field->isRequired()
        ]);
	}
}