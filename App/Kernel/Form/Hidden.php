<?php

namespace App\Kernel\Form;

class Hidden extends \App\Kernel\Back\Form
{
    public function html( $field, $name, $value = NULL )
    {
        return $this->View()->fetch( 'form/hidden.twig' , [
            'name' => $name,
            'value' => htmlspecialchars( $value )
        ]);
    }
}