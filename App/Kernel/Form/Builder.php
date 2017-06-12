<?php

namespace App\Kernel\Form;

class Builder extends \App\Kernel\Back\Form
{
    public function html( $field, $name, $value = NULL )
    {
        $this->value = $value ;

        $this->_lib_js  = [
            'cmsmedias/libs/lodash.min.js',
            'gridstack/dist/gridstack.min.js',
            'gridstack/dist/gridstack.jQueryUI.js',
            'cmsmedias/js/ckeditor/ckeditor.js',
            'cmsmedias/js/jbuilder.js'
        ];

        $this->_lib_css = [
            'gridstack/dist/gridstack.min.css',
            'cmsmedias/css/jbuilder.css'
        ];

        return $this->View()->fetch( 'form/builder.twig.html' , [
            'widthMax' => $field->getData('widthMax'),
            'value' => $this->value
        ]);
    }
}