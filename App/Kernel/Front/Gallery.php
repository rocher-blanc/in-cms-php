<?php

namespace App\Kernel\Front;

class Gallery extends \App\Kernel\Common\Gallery
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */


    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */


    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */


    /* ************************************************** */
    /* *****************  FUNCTIONS  ******************** */
    /* ************************************************** */

    public function getAllByField()
    {
        $rst = \DB::for_table('gallery')
            ->select('gallery_id')
            ->select('gallery_name')
            ->where_equal( 'gallery_module_id' , $this->getModuleId() )
            ->where_equal( 'gallery_element_id' , $this->getElementId() )
            ->where_equal( 'gallery_field' , $this->getField() )
            ->order_by_asc('gallery_position')
            ->find_many();

        $tab = [];

        $field = \App\Kernel\Container::getInstance()->module( $this->getModuleName() )->getEntity()->get( $this->getField() );
        $path  = str_replace( WEB_PATH , \App\Kernel\Http::getInstance()->getUrl() , IMAGE_PATH ) . '/' . $this->getFolder() . '/' ;

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab[ $row->gallery_id ]['source']  = $path . $row->gallery_name ;
                $tab[ $row->gallery_id ]['100x100'] = $path . $this->getMini( $row->gallery_name , 100 , 100 ) ;

                if ( $field->hasThumb() )
                {
                    foreach( $field->getThumb() as $thumb )
                    {
                        $tab[ $row->gallery_id ][$thumb[0] . 'x' . $thumb[1]] = $path . $this->getMini( $row->gallery_name , $thumb[0] , $thumb[1] ) ;
                    }
                }

                if ( $field->hasCrop() )
                {
                    foreach( $field->hasCrop() as $crop )
                    {
                        $tab[ $row->gallery_id ][$crop[0] . 'x' . $crop[1]] = $path . $this->getMini( $row->gallery_name , $crop[0] , $crop[1] , 'c' ) ;
                    }
                }
            }
        }

        return $tab ;
    }
}