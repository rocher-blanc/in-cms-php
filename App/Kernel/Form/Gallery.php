<?php

namespace App\Kernel\Form;

class Gallery extends \App\Kernel\Back\Form
{
    public $min_height = 1 ;
    public $min_width  = 1 ;

    public function html( $field, $name, $value = NULL )
    {
        $this->value = $value ;

        $this->_lib_js  = [
            'dropzone/dist/min/dropzone.min.js',
            'jcrop/js/jquery.Jcrop.min.js',
            'cmsmedias/js/jgallery.js'
        ];

        $this->_lib_css = [
            'dropzone/dist/min/basic.min.css',
            'dropzone/dist/min/dropzone.min.css',
            'jcrop/css/jquery.Jcrop.min.css',
            'cmsmedias/css/jgallery.css'
        ];

        $Gal = new \App\Kernel\Back\Gallery;
        $Gal->setElementId( ( $value == '' ? 0 : $value ) );
        $Gal->setModuleId( \App\Kernel\Container::getInstance()->module( $field->getData('module') )->getController(true)->getEntityId() );
        $Gal->setField( $field->getName() );
        $Gal->setFolder( \App\Kernel\Container::getInstance()->module( $field->getData('module') )->getEntity()->getFolder() );
        $rst = $Gal->getAllByField();

        return $this->View()->fetch( 'form/gallery.twig.html' , [
            'rst' => $rst,
            'name' => $name,
            'value' => $value,
            'field_name' => $field->getName(),
            'hasCrop' => $field->hasCrop(),
            'module' => $field->getData('module')
        ]);

        $html = '
        <input type="hidden" value="' . $value . '" name="' . $name . '" id="id_element_dropzone" />
        <input type="hidden" value="' . $field->getName() . '" name="field_dropzone" id="field_dropzone" />
		<div id="gallery_' . $field->getName() . '" class="blockGallery" data-crop="' . ( $field->hasCrop() ? '1' : '0' ) . '" data-value="' . $value . '" data-url="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/jgallery_order' ) . '" data-field="' . $field->getName() . '">
            <a class="btn btn-info btnAdd" data-jgallery data-field="' . $field->getName() . '" data-fieldid="id_' . $name . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/jgallery' ) . '">
                <i class="fa fa-plus"></i> Ajouter
            </a>' ;

        if ( $rst )
        {
            foreach( $rst as $key => $img )
            {
                $crop = '' ;

                if ( $field->hasCrop() )
                {
                    $crop = '<a href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/jgallery_crop' ) . '" class="crop" data-field="' . $field->getName() . '" data-image-id="' . $key . '"><i class="fa fa-crop"></i></a>&nbsp;&nbsp;' ;
                }

                $html.= '
                <div class="mini-img" id="gallery-' . $key . '">
                    <img src="' . $img['100x100'] . '"/>
                    <div class="btn-bar">
                        <a href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/jgallery_delete' ) . '" class="delete" data-image-id="' . $key . '"><i class="fa fa-trash"></i></a>&nbsp;&nbsp;
                        <i class="fa fa-arrows move"></i>&nbsp;&nbsp;
                        '  . $crop . '
                        <i class="fa fa-info-circle" data-toggle="tooltip" title="' . $img['size'] . ' | ' . $img['name'] . '"></i>
                    </div>
                </div>
                ' ;
            }
        }

        $html.= '<div class="clearGallery"></div></div>' ;

        return $html;
    }
}