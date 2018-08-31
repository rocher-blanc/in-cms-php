<?php

namespace App\Kernel\Form;

class Gallery extends \App\Kernel\Back\Form
{
    public $min_height = 1 ;
    public $min_width  = 1 ;

    public function html( $field, $name, $value = NULL )
    {
        $this->value = $value ;

        $Gal = new \App\Kernel\Back\Gallery;
        $Gal->setElementId( ( $value == '' ? -1 : $value ) );
        $Gal->setModuleId( \App\Kernel\Container::getInstance()->module( $field->getData('module') )->getController(true)->getEntityId() );
        $Gal->setField( $field->getName() );
        $Gal->setFolder( \App\Kernel\Container::getInstance()->module( $field->getData('module') )->getEntity()->getFolder() );
        $rst = $Gal->getAllByField();

        return $this->View()->fetch( 'form/gallery.twig' , [
            'rst' => $rst,
            'name' => $name,
            'value' => $value,
            'field_name' => $field->getName(),
            'hasCrop' => $field->hasCrop(),
            'module' => $field->getData('module')
        ]);
    }
}