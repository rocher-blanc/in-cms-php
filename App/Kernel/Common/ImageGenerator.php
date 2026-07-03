<?php

namespace App\Kernel\Common;

use App\Kernel\Exception;
use App\Kernel\Factory;
use App\Kernel\Http;
use claviska\SimpleImage;

class ImageGenerator
{
	private $folder;
	private $originalName;
	private $isGallery;

	public function __construct( $folder , $originalName , $isGallery = false )
	{
		$this->folder       = $folder;
		$this->originalName = $originalName;
		$this->isGallery    = $isGallery;
	}

	public function genImages( $field )
	{
		if ( ! empty( $field->getThumb() ) )
		{
			foreach( $field->getThumb() as $i )
			{
				$i = array_values($i);
				$this->genImage( "t" , $i[0], $i[1] , "thumb" );
			}
		}
		if ( ! empty( $field->getCover() ) )
		{
			foreach( $field->getCover() as $i )
			{
				$i = array_values($i);
				$this->genImage( "t" , $i[0], $i[1] , "cover" );
			}
		}
		if ( ! empty( $field->getWidth() ) )
		{
			foreach( $field->getWidth() as $i )
			{
				if( is_array($i) )
				{
					$i = array_values($i)[0];
				}
				$this->genImage( "w" , $i , null , "width" );
			}
		}
		if ( ! empty( $field->getHeight() ) )
		{
			foreach( $field->getHeight() as $i )
			{
				if( is_array($i) )
				{
					$i = array_values($i)[0];
				}
				$this->genImage( "h" , null , $i , "height" );
			}
		}
	}

	private function beforeSaveImage( $basePath, $dir , $name )
	{
		$currentPath = "/" . trim( $basePath , "/" );
		foreach( explode("/" , $dir) as $currentDir )
		{
			if ( strlen(trim($currentDir)) > 0 )
			{
				$currentPath .= "/" . $currentDir;
				if( ! is_dir($currentPath) )
				{
					mkdir( $currentPath );
				}
			}
		}

		$currentPath .= "/" . trim($name, "/");

		if( file_exists($currentPath) )
		{
			unlink( $currentPath );
		}
	}

	public static function updateName( $name , $addStr = "" )
	{
		$exp 	= explode( "." , $name ) ;
		$ext 	= end( $exp ) ;
		$extlen = ( strlen( $ext ) + 1 ) * -1 ;
		if ( $addStr != "" ) $addStr = "-" . $addStr ;

		$name = substr( $name , 0 , $extlen ) ;
		$name = $name . $addStr . "." . $ext ;

		return $name ;
	}

	public function getImages( $field, $path )
	{
		$rst  = [];
		$path = trim( $path , "/" );

		$rst['source']  = $path . '/' . $this->originalName;
		$rst['100x100'] = $path . '/' . $this->getThumbnailName( 100 , 100 , "t" );

		if ( $field->hasThumb() )
		{
			$rst['thumb'] = [];
			foreach( $field->getThumb() as $thumb )
			{
				$img = $this->getImagePath( "t" , $thumb[0] , $thumb[1] );
				if( $this->isGallery )  $rst[$thumb[0].'x'.$thumb[1]]          = $img;
				else                    $rst['thumb'][$thumb[0].'x'.$thumb[1]] = $img;
			}
		}

		if ( $field->hasCover() )
		{
			foreach( $field->getCover() as $cover )
			{

				$img = $this->getImagePath( "t" , $cover[0] , $cover[1] );
				if( $this->isGallery )  $rst[$cover[0].'x'.$cover[1]]          = $img ;
				else                    $rst['thumb'][$cover[0].'x'.$cover[1]] = $img ;
			}
		}

		if ( $field->hasWidth() )
		{
			foreach( $field->getWidth() as $width )
			{
				$img = $this->getImagePath( "w" , $width , NULL );
				if( $this->isGallery )  $rst['w'.$width]      = $img ;
				else                    $rst['width'][$width] = $img ;
			}
		}

		if ( $field->hasHeight() )
		{
			foreach( $field->getHeight() as $height )
			{
				$img = $this->getImagePath( "h" , NULL , $height );
				if( $this->isGallery )  $rst['h'.$height]       = $img ;
				else                    $rst['height'][$height] = $img ;
			}
		}

		return $rst;
	}

	protected function getThumbnailName( $width , $height = false , $folder = "t" )
	{
		$exp 	= explode( "." , $this->originalName ) ;
		$ext 	= end( $exp ) ;
		$extlen = ( strlen( $ext ) + 1 ) * -1 ;
		$name   = substr( $this->originalName , 0 , $extlen ) ;

		if ( empty( $name ) ) return false ;

		return $height
			? $folder . '/' . $name . "-" . $width . "x" . $height . "." . $ext
			: $folder . '/' . $name . "-" . $width . "." . $ext ;
	}

	protected function getImagePath( $type , $w , $h )
	{
		$mini = $this->getThumbnailName( $w , $h , $type );
		if ( $mini === false )
		{
			// Nom de fichier vide (edge case) : rien à générer ni à localiser.
			// Corrige le warning "Undefined variable $img".
			return false ;
		}

		$img  = trim( $this->folder . '/' . $mini , "/" );
		$mini = str_replace( WEB_PATH , \App\Kernel\Http::getInstance()->getUrl() , IMAGE_PATH ) . "/" . $img ;

		$path = IMAGE_PATH . "/" . $img;
		if( ! file_exists($path) )
		{
			switch( $type )
			{
				case "t" : $fullType = "cover" ; break;
				case "w" : $fullType = "width" ; break;
				case "h" : $fullType = "height"; break;
				default  : $fullType = "cover" ; break;
			}
			$this->genImage( $type , $w , $h , $fullType ) ;
		}

		return $mini ;
	}

	public function genImage( $dir , $width , $height , $type )
	{
		try {
			$path         = IMAGE_PATH . '/' . $this->folder . '/' ;
			$originalPath = $path . $this->originalName ;

			if( file_exists($originalPath) )
			{
				$oldImg = ( new SimpleImage() )->fromFile( $originalPath );

				list( $outputSuffix , $img ) = $this->dispatchProcessing( $type , $oldImg , $width , $height );

				$output     = $this->updateName( $this->originalName , $outputSuffix ) ;
				$outputPath = $path . trim($dir, "/") . "/" . trim($output, "/") ;
				$this->beforeSaveImage( $path , $dir , $outputPath );
                $exps = explode( "." , $outputPath );
                if ( $exps )
                {
                    $ext = end( $exps );

                    switch( $ext )
                    {
                        case "png" :
                        case "PNG" :
                            $img->toFile($outputPath);
                            break;
                        default :
                            $img->toFile($outputPath , "image/jpeg" , IMAGE_QUALITY);
                            break;
                    }
                }
				return $output ;
			}
		}
		catch( Exception $e ) {
			echo 'Error: ' . $e->getMessage();
		}

		return false;
	}

	private function dispatchProcessing( $type , $oldImg , $width , $height ) {
		switch( $type )
		{
			case "thumb":
				return [
					"{$width}x{$height}",
					$this->genThumb( $oldImg , $width, $height )
				];

			case "cover":
				return [
					"{$width}x{$height}",
					$this->genCover( $oldImg , $width, $height )
				];

			case "width":
				return [
					"{$width}",
					$this->genWidth( $oldImg , $width, $height )
				];

			case "height":
				return [
					"{$height}",
					$this->genHeight( $oldImg , $width, $height )
				];
		}
	}

	private function genThumb( $img , $width , $height )
	{
		return ( new SimpleImage() )
			->fromNew($width, $height, $this->getBgcolor($img))
			->overlay( $img->bestFit( $width , $height , "center" ) );
	}

	private function genCover( $img , $width , $height )
	{
		return ( new SimpleImage() )
			->fromNew($width, $height, $this->getBgcolor($img))
			->overlay( $img->thumbnail( $width , $height , "center" ) );
	}

	private function genWidth( $img , $width , $height )
	{
		$oldW   = $img->getWidth();
		$oldH   = $img->getHeight();
		$height = round( $width * $oldH / $oldW , 0 );

		return ( new SimpleImage() )
			->fromNew($width, $height, $this->getBgcolor($img))
			->overlay( $img->thumbnail( $width , $height , "center" ) );
	}

	private function genHeight( $img , $width , $height )
	{
		$oldW   = $img->getWidth();
		$oldH   = $img->getHeight();
		$width = round( $height * $oldW / $oldH , 0 );

		return ( new SimpleImage() )
			->fromNew($width, $height, $this->getBgcolor($img))
			->overlay( $img->thumbnail( $width , $height , "center" ) );
	}

	private function getBgcolor( $img ) {
		return $img->getMimeType() == "image/png"
			? 'transparent'
			: BACKGROUND_COLOR_THB ;
	}

}