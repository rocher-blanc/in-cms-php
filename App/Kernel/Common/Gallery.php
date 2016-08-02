<?php

namespace App\Kernel\Common;

class Gallery
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $image_name = NULL ;
    protected $folder_name = NULL ;
    protected $module_id = NULL ;
    protected $element_id = NULL ;
    protected $field = NULL ;

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

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getFolder()
    {
        return $this->folder_name ;
    }

    public function getModuleId()
    {
        return $this->folder_name ;
    }

    public function getElementId()
    {
        return $this->folder_name ;
    }

    public function getField()
    {
        return $this->field ;
    }

    public function getImageName()
    {
        return $this->image_name ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function getAllByField()
    { 
        $rst = \DB::for_table('jgallery')
            ->select('gallery_name')
            ->select('gallery_id')
            ->where_equal( 'gallery_module_id' , $this->getModuleId() )
            ->where_equal( 'gallery_element_id' , $this->getElementId() )
            ->where_equal( 'gallery_field' , $this->getField() )
            ->find_many();

        $tab = [];

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab[ $row->gallery_id ]['source']  = $row->gallery_name ;
                $tab[ $row->gallery_id ]['100x100'] = $this->getMini( $row->gallery_name , 100 , 100 ) ;
            }
        }

        return $tab ;
    }

    public function getMini( $name , $width , $height )
    {
        $exp 	= explode( "." , $name ) ;
        $ext 	= end( $exp ) ;
        $extlen = ( strlen( $ext ) + 1 ) * -1 ;
        $name   = substr( $name , 0 , $extlen ) ;

        if ( empty( $name ) ) return false ;

        return 't/' . $name . "-" . $width . "x" . $height . "." . $ext ;
    }
}