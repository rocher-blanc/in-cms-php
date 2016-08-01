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
        $html = '
		<div id="bloc_gallery_id_' . $name . '" class="blockGallery">
            <a class="btn btn-info btnAdd openGallery" data-jgallery data-field="' . $field->getName() . '" data-fieldid="id_' . $name . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/jgallery' ) . '">
                <i class="fa fa-plus"></i> Ajouter
            </a>
	    </div>
	    ' ;

        return $html;
    }
}