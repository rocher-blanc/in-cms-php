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

	/*----------------------------------------------------------------------*/
	/*----------                                                  ----------*/
	/*----------                       TOOLS                      ----------*/
	/*----------                                                  ----------*/
	/*----------------------------------------------------------------------*/

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
		return \App\Kernel\Config::getInstance() ; // migrated from SlimBridge
	}

	protected function post( $key )
	{
		return (\App\Kernel\AppContext::request()?->getParsedBody()[$key] ?? '');
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