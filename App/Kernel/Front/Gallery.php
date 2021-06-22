<?php

namespace App\Kernel\Front;

use App\Kernel\Common\ImageGenerator;

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

        if ( $rst )
        {
			$field = \App\Kernel\Container::getInstance()->module( $this->getModuleName() )->getEntity()->get( $this->getField() );
			$path  = str_replace( WEB_PATH , \App\Kernel\Http::getInstance()->getUrl() , IMAGE_PATH ) . '/' . $this->getFolder() . '/' ;

            foreach( $rst as $row )
            {
            	$generator = new ImageGenerator( $field->getEntityName() , $row->gallery_name , true );
            	$tab[$row->gallery_id] = array_merge(
            		[
            			'name' => $row->gallery_name
					],
            		$generator->getImages( $field , $path )
				);
            }
        }

        return $tab ;
    }
}