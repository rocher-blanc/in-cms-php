<?php

namespace App\Kernel\Form;

class Textarea extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
    {
        if ( $field->getData('editor') == true )
        {
            $this->_lib_js  = [
                'cmsmedias/canvas/js/wysiwyg/summernote.min.js',
                'cmsmedias/canvas/js/wysiwyg/summernote-image-attributes.js',
                'cmsmedias/canvas/js/wysiwyg/lang/summernote-fr-FR.js'
            ];

            $this->_lib_css  = [
                'cmsmedias/canvas/css/src/wysiwyg/summernote.css'
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