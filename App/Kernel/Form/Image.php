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
		$this->value 	= $value ;
		$this->_lib_js  = [
			'cmsmedias/canvas/js/components/bs-filestyle.js',
			'cmsmedias/canvas/js/easydoor/locales/fileinput/fr.js',
		];
		$this->_lib_css = ['cmsmedias/canvas/css/src/components/bs-filestyle.css'];
		
		if ( $this->hasValue() ) 
		{
			$this->_media->setImageId( $value );
			$this->_media->getNameById();
		}

		$mini = $field->getData('folder') . '/' . $this->_media->getMini( $this->_media->getImageName() , 't' , 100 , 100 ) ;

        $alt_img = [];
        if ( $field->getData('hasAltText') == true )
        {
            $Alt = new \App\Kernel\Back\Alt;
            $Alt->setElementId( $this->getElementId() );
            $Alt->setModuleId( $this->getModuleId() );
            $Alt->setFieldName( $field->getName() );
            $alt_img = $Alt->getOne();
        }

        return $this->View()->fetch( 'form/image.twig' , [
            'name' => $name,
            'has_alt_img' => $field->getData('hasAltText'),
            'alt_img' => $alt_img,
            'lang' => \App\Kernel\Lang::getInstance()->getAll(),
            'crop' => $this->getBlocCrop( $field , $value ),
            'thumb' => $this->getBlocThumb( $field , $value ),
            'hasValue' => $this->hasValue(),
            'minWidth' => $this->min_width,
            'minHeight' => $this->min_height,
            'hrefLink' => $this->Factory()->Url()->route( $field->getData('module') , 'media' ),
            'image' => ( file_exists( WEB_PATH . $mini ) ? $this->Factory()->Url()->get( $mini , true ) : \App\Kernel\Http::getInstance()->assetAdmin('img/image-not-found.jpg') ),
            'value' => ( $this->validValue( $mini ) == true ? $value : '' ),
            'field_name' => $field->getName()
        ]);
	}
	
	private function getBlocThumb( $field , $value )
	{
        $tab = [] ;

        if ( $field->hasThumb() )
		{
			foreach( $field->getThumb() as $thumb )
			{
				if ( $thumb[0] > $this->min_width ) $this->min_width  = $thumb[0] ;
				if ( $thumb[1] > $this->min_height ) $this->min_height  = $thumb[1] ;
	
				$mini = $field->getData('folder') . '/' . $this->_media->getMini( $this->_media->getImageName() , 't' , $thumb[0] , $thumb[1] ) ;

                $tab[] = [
                    "image" => ( file_exists( WEB_PATH . $mini ) ? $this->Factory()->Url()->get( $mini , true ) : $this->Factory()->Url()->get('assets/themes/default/img/image-not-found.jpg') ),
                    "width" => $thumb[0],
                    "height" => $thumb[1]
                ];
			}
		}
		
		return $tab ;
	}
	
	private function getBlocCrop( $field , $value )
	{
        $tab = [] ;

        if ( $field->hasCrop() )
		{
			foreach( $field->getCrop() as $crop )
			{
				if ( $crop[0] > $this->min_width ) $this->min_width  = $crop[0] ;
				if ( $crop[1] > $this->min_height ) $this->min_height  = $crop[1] ;
	
				$mini = $field->getData('folder') . '/' . $this->_media->getMini( $this->_media->getImageName() , 'c' , $crop[0] , $crop[1] ) ;

                $disabled = false ;

                if ( ( $this->hasValue() == true && file_exists( WEB_PATH . $mini ) == false ) or $this->hasValue() == false )
                {
                    $disabled = true ;
                }

                $tab[] = [
                    "image" => ( file_exists( WEB_PATH . $mini ) ? $this->Factory()->Url()->get( $mini , true ) : $this->Factory()->Url()->get('assets/themes/default/img/image-not-found.jpg') ),
                    "disabled" => $disabled,
                    "href" => $this->Factory()->Url()->route( $field->getData('module') , 'crop' , '' , $value ),
                    "width" => $crop[0],
                    "height" => $crop[1]
                ];
			}
		}

        return $tab ;
	}
}