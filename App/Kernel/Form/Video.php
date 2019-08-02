<?php

namespace App\Kernel\Form;

class Video extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
        return $this->View()->fetch( 'form/video.twig' , [
            'name' => $name,
            'value' => $value,
            'required' => $field->isRequired()
        ]);
	}
}