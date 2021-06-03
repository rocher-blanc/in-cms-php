<?php

namespace App\Kernel\Common;

use App\Kernel\Exception;
use App\Kernel\Factory;
use claviska\SimpleImage;

class ImageGenerator
{
	private $folder;
	private $originalName;

	public function __construct( $folder , $originalName )
	{
		$this->folder       = $folder;
		$this->originalName = $originalName;
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
		$name = Factory::getInstance()->Url()->encode( $name . $addStr ) . "." . $ext ;

		return $name ;
	}

	public function genImage( $dir , $width , $height , $type )
	{
		try {
			$path         = IMAGE_PATH . '/' . $this->folder . '/' ;
			$originalPath = $path . $this->originalName ;

			$oldImg = ( new SimpleImage() )->fromFile( $originalPath );

			list( $outputSuffix , $img ) = $this->dispatchProcessing( $type , $oldImg , $width , $height );

			$output     = $this->updateName( $this->originalName , $outputSuffix ) ;
			$outputPath = $path . trim($dir, "/") . "/" . trim($output, "/") ;
			$this->beforeSaveImage( $path , $dir , $outputPath );
			$img->toFile($outputPath);

			return $output ;

		} catch( Exception $e ) {
			echo 'Error: ' . $e->getMessage();
		}
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