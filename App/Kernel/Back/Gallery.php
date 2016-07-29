<?php

namespace App\Kernel\Back;

class Gallery extends \App\Kernel\Common\Gallery
{
    public function add()
    {
        $gallery = \DB::for_table('gallery')->create();
        $gallery->gallery_name = $this->getImageName();
        $gallery->gallery_module_id = $this->getImageName();
        $gallery->gallery_element_id = $this->getImageName();
        $gallery->gallery_field = $this->getImageName();
    }
}