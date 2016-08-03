<?php

namespace App\Kernel\Form;

class Gallery extends \App\Kernel\Back\Form
{
    public $min_height = 1 ;
    public $min_width  = 1 ;

    public function __construct()
    {

    }

    public function html( $field, $name, $value = NULL )
    {
        $this->value = $value ;

        $this->_lib_js  = [
            'dropzone/dist/min/dropzone.min.js',
            'cmsmedias/js/jgallery.js'
        ];

        $this->_lib_css = [
            'dropzone/dist/min/basic.min.css',
            'dropzone/dist/min/dropzone.min.css',
            'cmsmedias/css/jgallery.css'
        ];
/*
        $html = '
		<div id="bloc_gallery_id_' . $name . '" class="blockGallery">
            <form action="/upload" class="dropzone needsclick dz-clickable" id="demo-upload">

              <div class="dz-message needsclick">
                Drop files here or click to upload.<br>
                <span class="note needsclick">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>
              </div>

            </form>
	    </div>
	    ' ;
*/

        $Gal = new \App\Kernel\Back\Gallery;
        $Gal->setElementId( ( $value == '' ? NULL : $value ) );
        $Gal->setModuleId( \App\Kernel\Container::getInstance()->module( $field->getData('module') )->getController(true)->getEntityId() );
        $Gal->setField( $field->getName() );
        $Gal->setFolder( \App\Kernel\Container::getInstance()->module( $field->getData('module') )->getEntity()->getFolder() );
        $rst = $Gal->getAllByField();

        //\App\Kernel\Debug::save($rst);
        //\App\Kernel\Debug::view();


        $html = '
        <input type="hidden" value="' . $value . '" name="' . $name . '" id="id_element_dropzone" />
        <input type="hidden" value="' . $field->getName() . '" name="field_dropzone" id="field_dropzone" />
		<div id="gallery_' . $field->getName() . '" class="blockGallery">
            <a class="btn btn-info btnAdd" data-jgallery data-field="' . $field->getName() . '" data-fieldid="id_' . $name . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/jgallery' ) . '">
                <i class="fa fa-plus"></i> Ajouter
            </a>' ;

        if ( $rst )
        {
            foreach( $rst as $key => $img )
            {
                $html.= '
                <div class="mini-img">
                    <img src="' . $img['100x100'] . '" id="gallery-img-' . $key . '"/>
                    <div class="btn-bar">
                        <i class="fa fa-trash"></i>&nbsp;&nbsp;
                        <i class="fa fa-arrows"></i>&nbsp;&nbsp;
                        <i class="fa fa-info-circle"></i>
                    </div>
                </div>
                ' ;
            }
        }
        $html.= '<div class="clearGallery"></div></div>' ;

        return $html;
    }
}