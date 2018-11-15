<?php

namespace App\Kernel\Form;

class Icon extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
        $this->_cdn_css = $field->getData('libCss');

        return $this->View()->fetch( 'form/icon.twig' , [
            'name' => $name,
            'value' => htmlspecialchars( $value ),
            'listIcons' => $field->getData('listIcons'),
            'required' => $field->isRequired()
        ]);
	}
}