<?php

namespace App\Kernel\Common;

use App\Kernel\Exception;

class Gallery
{

    protected $image_name   = NULL ;
    protected $folder_name  = NULL ;
    protected $module_name  = NULL ;
    protected $module_id    = NULL ;
    protected $element_id   = NULL ;
    protected $image_id     = NULL ;
    protected $field        = NULL ;

    public function __construct() {}

	/*----------------------------------------------------------------------*/
	/*----------                                                  ----------*/
	/*----------                      SETTERS                     ----------*/
	/*----------                                                  ----------*/
	/*----------------------------------------------------------------------*/

    public function setFolder( $var )
    {
        $this->folder_name = $var ;
    }

    public function setImageId( $var )
    {
        $this->image_id = $var ;
    }

    public function setModuleId( $var )
    {
        $this->module_id = $var ;
    }

    public function setElementId( $var )
    {
        $this->element_id = $var ;
    }

    public function setField( $var )
    {
        $this->field = $var ;
    }

    public function setImageName( $var )
    {
        $this->image_name = $var ;
    }

    public function setModuleName( $var )
    {
        $this->module_name = $var ;
    }

    /*----------------------------------------------------------------------*/
    /*----------                                                  ----------*/
    /*----------                      GETTERS                     ----------*/
    /*----------                                                  ----------*/
    /*----------------------------------------------------------------------*/

    public function getFolder()
    {
        return $this->folder_name ;
    }

    public function getModuleId()
    {
        return $this->module_id ;
    }

    public function getImageId()
    {
        return $this->image_id ;
    }

    public function getElementId()
    {
        return $this->element_id ;
    }

    public function getField()
    {
        return $this->field ;
    }

    public function getImageName()
    {
        return $this->image_name ;
    }

    public function getModuleName()
    {
        return $this->module_name ;
    }

    /*----------------------------------------------------------------------*/
    /*----------                                                  ----------*/
    /*----------                  IMAGE GENERATION                ----------*/
    /*----------                                                  ----------*/
    /*----------------------------------------------------------------------*/

	/*
    protected function beforeSaveImage( $basePath, $dir , $name )
	{
		$currentPath = "/" . trim( $basePath , "/" );
		foreach( explode("/" , $dir) as $currentDir )
		{
			if( strlen(trim($currentDir)) > 0 )
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

	public function genImage( $dir , $newNameSuffix , $callable , $name = '' )
	{
		if ( empty( $name ) )
		{
			$name = $this->getImageName() ;
		}
		try {
			$path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
			$img  = $path . $name ;
			$miniName = $this->updateName( $name , $newNameSuffix ) ;
			$file     = $path . trim($dir, "/") . "/" . trim($miniName, "/") ;
			$tmpImg   = new SimpleImage( $img );
			$this->beforeSaveImage( $path , $dir , $miniName );
			$callable( $tmpImg , $file );
			return $miniName ;

		} catch( Exception $e ) {
			echo 'Error: ' . $e->getMessage();
		}
	}

	public function genThumb( $width , $height , $crop = false , $name = '' )
	{
		return $this->genImage( $crop ? "c" : "t" , "{$width}x{$height}" , function( $tmpImg , $file ) use ($width, $height) {
			$tmpImg->best_fit( $width , $height , "center" );
			$destImg = new SimpleImage(null, $width, $height, BACKGROUND_COLOR_THB);
			$destImg->overlay($tmpImg)->save($file);
		}, $name );
	}

	public function genCover( $width , $height , $crop = false , $name = '' )
	{
		return $this->genImage( $crop ? "c" : "t" , "{$width}x{$height}" , function( $tmpImg , $file ) use ($width, $height) {
			$tmpImg->thumbnail( $width , $height , "center" );
			$destImg = new SimpleImage(null, $width, $height, BACKGROUND_COLOR_THB);
			$destImg->overlay($tmpImg)->save($file);
		}, $name );
	}

	public function genWidth( $width , $name = '' )
	{
		return $this->genImage( "w" , "$width" , function( $tmpImg , $file ) use ($width) {
			$oldW   = $tmpImg->get_width();
			$oldH   = $tmpImg->get_height();
			$height = round( $width * $oldH / $oldW , 0 );

			$tmpImg->thumbnail( $width , $height , "center" );
			$destImg = new SimpleImage(null, $width, $height, BACKGROUND_COLOR_THB);
			$destImg->overlay($tmpImg)->save($file);
		}, $name );
	}

	public function genHeight( $height , $name = '' )
	{
		return $this->genImage( "h" , "$height" , function( $tmpImg , $file ) use($height) {
			$oldW  = $tmpImg->get_width();
			$oldH  = $tmpImg->get_height();
			$width = round( $height * $oldW / $oldH , 0 );

			$tmpImg->thumbnail( $height , $height , "center" );
			$destImg = new SimpleImage(null, $width, $height, BACKGROUND_COLOR_THB);
			$destImg->overlay($tmpImg)->save($file);
		}, $name );
	}
	*/


	/*----------------------------------------------------------------------*/
	/*----------                                                  ----------*/
	/*----------                       TOOLS                      ----------*/
	/*----------                                                  ----------*/
	/*----------------------------------------------------------------------*/

	/*
	protected function updateName( $name , $addStr = "" )
	{
		$exp 	= explode( "." , $name ) ;
		$ext 	= end( $exp ) ;
		$extlen = ( strlen( $ext ) + 1 ) * -1 ;
		if ( $addStr != "" ) $addStr = "-" . $addStr ;

		$name = substr( $name , 0 , $extlen ) ;
		$name = $this->Factory()->Url()->encode( $name . $addStr ) . "." . $ext ;

		return $name ;
	}
	*/

	public function getMini( $name , $width , $height = false , $type = "t" )
	{
		$exp 	= explode( "." , $name ) ;
		$ext 	= end( $exp ) ;
		$extlen = ( strlen( $ext ) + 1 ) * -1 ;
		$name   = substr( $name , 0 , $extlen ) ;

		if ( empty( $name ) ) return false ;

		return $height
			? $type . '/' . $name . "-" . $width . "x" . $height . "." . $ext
			: $type . '/' . $name . "-" . $width . "." . $ext ;
	}

	protected function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}

	protected function CMS()
	{
		return \App\Kernel\CMS::getInstance() ;
	}

	protected function getApp()
	{
		return \Slim\Slim::getInstance() ;
	}

	protected function post( $key )
	{
		return $this->getApp()->request->post( $key );
	}

	protected function formatBytes( $bytes )
	{
		if ( $bytes > 1000 )
		{
			// KO
			return number_format($bytes/1000, 0, '.', ' ') . " Ko";
		}
		else if ( $bytes > 1000000 )
		{
			// MO
			return number_format($bytes/1000000, 0, '.', ' ') . " Mo";
		}
		else if ( $bytes > 1000000000 )
		{
			// GO
			return number_format($bytes/1000000000, 0, '.', ' ') . " Go";
		}
		else
		{
			// O
			return number_format($bytes, 0, '.', ' ') . " octets";
		}
	}

}