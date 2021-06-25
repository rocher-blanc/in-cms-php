<?php

namespace App\Kernel\Form;

class Textarea extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
    {
        return $this->View()->fetch( 'form/textarea.twig' , [
            'name' => $name,
            'value' => $value,
            'editor' => $field->getData('editor'),
            'editor_config' => $field->getData('editorConfig'),
            'column' => $field->getColumn(),
            'hasFlag' => $field->hasLang(),
            'flag' => $field->getData('flag'),
            'required' => $field->isRequired()
        ]);
    }
}