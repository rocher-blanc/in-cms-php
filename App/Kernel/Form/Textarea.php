<?php

namespace App\Kernel\Form;

class Textarea extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
    {
        if ( $field->getData('editor') == true )
        {
            $this->_lib_js  = [
                'cmsmedias/js/ckeditor/ckeditor.js',
                'cmsmedias/js/ckeditor.js'
            ];
        }

        return $this->View()->fetch( 'form/textarea.twig' , [
            'name' => $name,
            'value' => $value,
            'editor' => $field->getData('editor'),
            'column' => $field->getColumn(),
            'hasFlag' => $field->hasLang(),
            'flag' => $field->getData('flag'),
            'required' => $field->isRequired()
        ]);
    }
}