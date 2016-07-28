<?php

namespace App\Kernel\Form;

class Gallery extends \App\Kernel\Back\Form
{
	public $min_height = 1 ;
	public $min_width  = 1 ;
	
	public function __construct()
	{
		$this->_media = new \App\Kernel\Back\Media;
	}
	
	private function hasValue()
	{
		if ( $this->value === NULL or $this->value === 0 or $this->value === '0' ) 	return false ;
		else																		return true ;
	}
	
	private function validValue( $mini )
	{
        if ( $this->hasValue() && file_exists( WEB_PATH . $mini ) ) 	return true ;
		else															return false ;
	}
	
	public function html( $field, $name, $value = NULL )
	{
		$this->value = $value ;
		
		$this->_lib_js  = [
			'jquery-file-upload/js/vendor/jquery.ui.widget.js',
			'jquery-file-upload/js/jquery.iframe-transport.js',
			'jquery-file-upload/js/jquery.fileupload.js',
			'jcrop/js/jquery.Jcrop.min.js'
		];
		
		$this->_lib_css = [
			'jquery-file-upload/css/jquery.fileupload.css',
			'jcrop/css/jquery.Jcrop.min.css'
		];

		$html = '
		<div id="bloc_gallery_id_' . $name . '" class="block_gallery">
            <div class="img-source">
                <div class="blocImage" id="source_' . $field->getName() . '">' ;
                    if ( $this->hasValue() )
                    {
                        $html.= '<img src="' ;

                        if ( file_exists( WEB_PATH . $mini ) )	$html.= $this->Factory()->Url()->get( $mini , true ) ;
                        else									$html.= \App\Kernel\Http::getInstance()->assetAdmin('img/image-not-found.jpg');

                        $html.= '" class="img-responsive" />' ;
                    }
                    $html.= '
                </div>
                <div class="type">Nouvelle image</div>
                <a class="btn btn-info fileinput-button openMedia" style="width" data-field="' . $field->getName() . '" data-fieldid="id_' . $name . '" data-minwidth="' . $this->min_width . '" data-minheight="' . $this->min_height . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/media' ) . '">
                    <i class="fa fa-plus"></i> <span>Sélectionner</span>
                </a>
            </div>
	    </div>
	    ' ;
	
		return $html;
	}
}