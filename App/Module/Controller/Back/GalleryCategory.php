<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Media;
use App\Kernel\Front\Translate;

class GalleryCategory extends Controller
{
    public function drawAction()
    {
        $this->setRender( 'id' , $this->getId() );

        if ( $this->getApp()->request->isPost() )
        {
            $Media = new Media;
            $Media->setGalleryId( $this->getId() );
            $rst = $Media->uploadLib( UPLOAD_PATH ) ;

            if ( $rst )
            {
                return $this->Factory()->Response()->returnJSON( '' , true ) ;
            }
            else
            {
                return $this->Factory()->Response()->returnJSON( Translate::getInstance()->getText("Une erreur est survenue lors de l'upload") , false ) ;
            }
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