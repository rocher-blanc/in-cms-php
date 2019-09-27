<?php

namespace App\Kernel\Common;

use abeautifulsite\SimpleImage;

class Gallery
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $image_name   = NULL ;
    protected $folder_name  = NULL ;
    protected $module_name  = NULL ;
    protected $module_id    = NULL ;
    protected $element_id   = NULL ;
    protected $image_id     = NULL ;
    protected $field        = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

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

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

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

    /* ************************************************** */
    /* *****************    TOOLS     ******************* */
    /* ************************************************** */

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

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

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

    public function getMini( $name , $width , $height , $type = "t" )
    {
        $exp 	= explode( "." , $name ) ;
        $ext 	= end( $exp ) ;
        $extlen = ( strlen( $ext ) + 1 ) * -1 ;
        $name   = substr( $name , 0 , $extlen ) ;

        if ( empty( $name ) ) return false ;

        return $type . '/' . $name . "-" . $width . "x" . $height . "." . $ext ;
    }

    public function genThumb( $width , $height , $crop = false , $name = '' )
    {
        if ( empty( $name ) )
        {
            $name = $this->getImageName() ;
        }

        $path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
        $img  = $path . $name ;

        try {
            $miniName = $this->updateName( $name , $width . "x" . $height ) ;
            $file = $path . ( $crop == true ? 'c' : 't' ) . "/" . $miniName ;

            $tmpImg = new SimpleImage( $img );
            $tmpImg->best_fit( $width , $height );

            $destImg = new SimpleImage(null, $width, $height, BACKGROUND_COLOR_THB);
            $destImg->overlay($tmpImg)->save($file);

            return $miniName ;
        } catch(Exception $e) {
            echo 'Error: ' . $e->getMessage();
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
}