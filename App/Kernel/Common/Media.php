<?php

namespace App\Kernel\Common;

class Media
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	protected $image_id = NULL ;
	protected $image_name = NULL ;

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

	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */

	public function getImageId()
	{
		return $this->image_id ;
	}
	
	public function getImageName()
	{
		return $this->image_name ;
	}

	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */

    public function getNameById()
    {
        $rst = \DB::for_table('media')
            ->select('media_name')
            ->where_equal( 'media_id' , $this->getImageId() )
            ->find_one();

        if ( $rst ) $this->setImageName( $rst->media_name ) ;

        if ( $rst )	return true ;
        else		return false ;
    }

	public function getMini( $name , $type , $width , $height )
	{
		$exp 	= explode( "." , $name ) ;
		$ext 	= end( $exp ) ;
		$extlen = ( strlen( $ext ) + 1 ) * -1 ;
		$name   = substr( $name , 0 , $extlen ) ;

		if ( empty( $name ) ) return false ;

		return $type  . '/' . $name . "-".$width."x".$height."." . $ext ;
	}
}