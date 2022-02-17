<?php

namespace App\Kernel\Common;

use claviska\SimpleImage;
use App\Kernel\Back\Image;
use App\Kernel\Exception;

class Media
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $module_id    = NULL ;
    protected $module_name  = NULL ;
    protected $folder_name  = NULL ;
    protected $field        = NULL ;
	protected $image_id     = NULL ;
	protected $image_name   = NULL ;
    protected $gallery_id   = NULL ;

	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct() {}

	/* ************************************************** */
	/* ******************   SETTER   ******************** */
	/* ************************************************** */

    public function setImageId( $var )
    {
        $this->image_id = $var ;
    }

	public function setImageName( $var )
	{
		$this->image_name = $var ;
	}

    public function setModuleId( $var )
    {
        $this->module_id = $var ;
    }

    public function setModuleName( $var )
    {
        $this->module_name = $var ;
    }

    public function setFolder( $var )
    {
        $this->folder_name = $var ;
    }

    public function setField( $var )
    {
        $this->field = $var ;
    }

    public function setGalleryId( $var )
    {
        $this->gallery_id = $var ;
    }

	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */

    public function getGalleryId()
    {
        return $this->gallery_id ;
    }

	public function getImageId()
	{
		return $this->image_id ;
	}
	
	public function getImageName()
	{
		return $this->image_name ;
	}

    public function getModuleId()
    {
        return $this->module_id ;
    }

    public function getModuleName()
    {
        return $this->module_name ;
    }

    public function getFolder()
    {
        return $this->folder_name ;
    }

    public function getField()
    {
        return $this->field ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */

    public function getNameById()
    {
        $rst = \DB::for_table('media')
            ->select('media_module_id')
            ->select('media_gallery')
            ->select('media_name')
            ->where_equal( 'media_id' , $this->getImageId() )
            ->find_one();

        if ( $rst )
        {
            $this->setModuleId( $rst->media_module_id ) ;
            $this->setGalleryId( $rst->media_gallery ) ;
            $this->setImageName( $rst->media_name ) ;
        }

        if ( $rst )	return true ;
        else		return false ;
    }

	public function getMini( $name , $type , $width , $height = false )
	{
		$exp 	= explode( "." , $name ) ;
		$ext 	= end( $exp ) ;
		$extlen = ( strlen( $ext ) + 1 ) * -1 ;
		$name   = substr( $name , 0 , $extlen ) ;

		if ( empty( $name ) ) return false ;

		if( $width && $height )
		{
			return $type . '/' . $name . "-".$width."x".$height."." . $ext;
		}
		elseif( $width && ! $height )
		{
			return $type . '/' . $name . "-".$width."." . $ext;
		}
		elseif( ! $width && $height )
		{
			return $type . '/' . $name . "-".$height."." . $ext;
		}
		else
			{
			return "";
		}
	}

    public function rename()
    {
        $path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
        $img  = UPLOAD_PATH . '/' . $this->getImageName() ;

        if ( file_exists( $img ) )
        {
            $name = ImageGenerator::updateName( $this->getImageName() ) ;

            if ( file_exists( $path . $name ) ) $exist = true ;
            else							    		 $exist = false ;

            if ( $exist == true )
            {
                $i = 1;
                if ( strpos( "-" , $name ) !== false )
                {
                    $exp 	= explode( "-" , $name ) ;
                    $ct  	= count( $exp ) ;
                    $ext 	= $exp[ $ct - 1 ] ;
                    $extlen = ( strlen( $ext ) + 1 ) * -1 ;

                    $exp = str_replace( $this->getExtension( $name ) , "" , $name ) ;

                    if ( is_numeric( $ext ) )
                    {
                        $name = substr( $name , 0 , $extlen ) . $this->getExtension( $name ) ;
                        $i    = intval( $ext + 1 ) ;
                    }
                    else
                    {
                        $name = $this->updateName( $this->getImageName() ) ;
                    }
                }

                while( $exist == true )
                {
                    $newname = $this->updateName( $name , $i ) ;
                    if ( ! file_exists( $path . $newname ) )
                    {
                        $exist = false ;
                        $name  = $newname ;
                    }
                    $i++;
                }
            }

            if ( $path . $name != $img )
            {
                rename( $img , $path . $name ) ;

                $media = \DB::for_table('media')
                    ->where_id_is( $this->getImageId() )
                    ->find_one();

                $media->media_name = $name;
                $media->save() ;

                $this->setImageName( $name ) ;
            }

			$generator = new ImageGenerator( $this->getFolder(), $this->getImageName() );
            return $generator->genImage( "t" , 100 , 100 , "thumb" ) ;
        }
    }

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

	/*----------------------------------------------------------------------*/
	/*----------                                                  ----------*/
	/*----------                  IMAGE GENERATION                ----------*/
	/*----------                                                  ----------*/
	/*----------------------------------------------------------------------*/


}