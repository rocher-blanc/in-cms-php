<?php

namespace App\Kernel\Form;

class Video extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
//        $this->_lib_js  = ['cmsmedias/js/jvideo.js'];
//        $this->_lib_css = ['cmsmedias/css/jvideo.css'];

        return $this->View()->fetch( 'form/video.twig' , [
            'name' => $name,
            'value' => $value,
            'required' => $field->isRequired()
        ]);
	}
}