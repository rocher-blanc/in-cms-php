<?php

namespace App\Kernel\Back;

class Gallery extends \App\Kernel\Common\Gallery
{
    public function add()
    {


        $gallery = \DB::for_table('gallery')->create();
        $gallery->gallery_name = $_FILES['file']['name'];
        $gallery->gallery_type = $_FILES['file']['type'];
        $gallery->gallery_size = $_FILES['file']['size'];
        $gallery->gallery_module_id = $this->getModuleId();
        $gallery->gallery_element_id = $this->getElementId();
        $gallery->gallery_field = $this->getField();
        $gallery->save();
    }

    protected function getNewFilename( $img )
    {
        $path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
        $img  = $path . '/' . $img ;

        if ( ! file_exists( $img ) )
        {
            return $img ;
        }
        else
        {
            $exist = true ;
            while( $exist == true )
            {

            }
        }
    }
}