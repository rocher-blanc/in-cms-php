<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Media;

class GalleryCategory extends Controller
{
    public function drawAction()
    {
        if ( $this->getApp()->request->isPost() )
        {
            $Media = new Media;
            $rst = $Media->uploadLib( UPLOAD_PATH ) ;

            return $this->Factory()->Response()->printJSON( $rst ) ;

        }
        else
        {
            $Media = new Media;
            $Media->setGalleryId( $this->getId() );
            $images = $Media->getAllByGallery();

            $this->setRender( 'images' , $images );
            $this->render('upload.twig');
        }
    }
}