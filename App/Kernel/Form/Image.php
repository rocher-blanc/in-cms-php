<?php

namespace App\Kernel\Form;

class Image extends \App\Kernel\Back\Form
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
		
		if ( $this->hasValue() ) 
		{
			$this->_media->setImageId( $value );
			$this->_media->getNameById();
		}
		$mini = $field->getData('folder') . '/' . $this->_media->getMini( $this->_media->getImageName() , 't' , 100 , 100 ) ;
		
		$crop  = $this->getBlocCrop( $field , $value ) ;
		$thumb = $this->getBlocThumb( $field , $value ) ;
		
		$html = '
		<div id="bloc_media_id_' . $name . '">
		<input type="hidden" name="' . $name . '" id="id_' . $name . '" value="' . ( $this->validValue( $mini ) == true ? $value : '' ) . '" />
		<div class="scrollhimage">
	<div class="img-source">
		<div class="blocImage" id="source_' . $field->getName() . '">' ;
			if ( $this->hasValue() )
			{
				$html.= '<img src="' ;
				
				if ( file_exists( WEB_PATH . $mini ) )	$html.= $this->Factory()->Url()->get( $mini , true ) ;
				else									$html.= \App\Kernel\Http::getInstance()->assetAdmin('img/image-not-found.jpg');
				
				$html.= '" class="img-responsive" />' ;
			}	
		$html.= '</div>
		<div class="type">Source</div>
		<a class="btn btn-info fileinput-button openMedia" data-field="' . $field->getName() . '" data-fieldid="id_' . $name . '" data-minwidth="' . $this->min_width . '" data-minheight="' . $this->min_height . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/media' ) . '">
			<i class="fa fa-plus"></i>
			<span>Sélectionner</span>
		</a>
	</div>' . $crop . $thumb . '</div></div>' ;
	
		return $html;
	}
	
	private function getBlocThumb( $field , $value )
	{
		$html = '' ;
		if ( $field->hasThumb() )
		{
			foreach( $field->getThumb() as $thumb )
			{
				if ( $thumb[0] > $this->min_width ) $this->min_width  = $thumb[0] ;
				if ( $thumb[1] > $this->min_height ) $this->min_height  = $thumb[1] ;
	
				$mini = $field->getData('folder') . '/' . $this->_media->getMini( $this->_media->getImageName() , 't' , $thumb[0] , $thumb[1] ) ;
				$html.= '
					<div class="img-thumb">
						<div class="blocImage" id="t_' . $field->getName() . '_' . $thumb[0] . 'x' . $thumb[1] . '">' ;
				if ( $this->hasValue() )
				{
					$html.= '<img src="' ;
					
					if ( file_exists( WEB_PATH . $mini ) )	$html.= $this->Factory()->Url()->get( $mini , true ) ;
					else									$html.= $this->Factory()->Url()->get('assets/themes/default/img/image-not-found.jpg') ;
					
					$html.= '" class="img-responsive" />' ;
				}
				$html.= '</div>
						<div class="type">Taille: ' . $thumb[0] . 'x' . $thumb[1] . '</div>
					</div>' ;
			}
		}
		
		return $html ;
	}
	
	private function getBlocCrop( $field , $value )
	{
		$html = '' ;
		if ( $field->hasCrop() )
		{
			foreach( $field->getCrop() as $crop )
			{
				if ( $crop[0] > $this->min_width ) $this->min_width  = $crop[0] ;
				if ( $crop[1] > $this->min_height ) $this->min_height  = $crop[1] ;
	
				$mini = $field->getData('folder') . '/' . $this->_media->getMini( $this->_media->getImageName() , 'c' , $crop[0] , $crop[1] ) ;
				$html.= '
					<div class="img-crop">
						<div class="blocImage" id="c_' . $field->getName() . '_' . $crop[0] . 'x' . $crop[1] . '">' ;
				if ( $this->hasValue() )
				{
					$html.= '<img src="' ;
					
					if ( file_exists( WEB_PATH . $mini ) )	$html.= $this->Factory()->Url()->get( $mini , true ) ;
					else									$html.= $this->Factory()->Url()->get('assets/themes/default/img/image-not-found.jpg') ;
					
					$html.= '" class="img-responsive" />' ;
				}
				$html.= '</div>
						<div class="type">Taille: ' . $crop[0] . 'x' . $crop[1] . '</div>
						<a class="btn btn-info fileinput-button openCrop' ;
						
						if ( ( $this->hasValue() == true && file_exists( WEB_PATH . $mini ) == false ) or $this->hasValue() == false )
						{
							$html .= ' disabled' ;
						}
						
						$html.= '" data-height="'. $crop[1] .'" data-width="'. $crop[0] .'" data-field="' . $field->getName() . '" href="' . $this->Factory()->Url()->get( '/module/' . $field->getData('module') . '/crop/' . $value ) . '">
							<i class="fa fa-crop"></i>
							<span>Modifier</span>
						</a>
					</div>' ;
			}
		}
		
		return $html ;
	}
}