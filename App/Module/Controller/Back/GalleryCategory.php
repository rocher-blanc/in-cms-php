<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Media;

class GalleryCategory extends Controller
{
    public function drawAction()
    {
        $this->setRender( 'id' , $this->getId() );
        $this->setRender( 'lib_upload_msg' , $this->m('lib_upload_msg') );
        $this->setRender( 'lib_upload_doing' , $this->m('lib_upload_doing') );

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
                return $this->Factory()->Response()->returnJSON( $this->m('lib_upload_error') , false ) ;
            }
        }
        else
        {
            $Media = new Media;
            $Media->setGalleryId( $this->getId() );
            $Media->setFolder("_lib");
            $images = $Media->getAllByGallery();

            $this->setRender( 'images' , $images );
            $this->render('upload.twig');
        }
    }

    protected function deletemediaAction()
    {
        $this->checkToken() ;

        $Media = new Media;
        $Media->setImageId( $this->getId() );
        $Media->setFolder('_lib') ;

        $rst = $Media->delete();

        if ( $rst ) $this->Factory()->Response()->returnJSON( $this->m("deletemedia_success") , true ) ;
        else		$this->Factory()->Response()->returnJSON( $this->m("deletemedia_failed") ) ;
    }
}