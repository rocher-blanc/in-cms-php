<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;

class NewsletterModel extends Controller
{
    protected function drawAction()
    {
        $this->setRender('id' , $this->getId() );
        $this->setRender('apiKey' , TOPOL_API_KEY);
        $this->setRender('userId' , TOPOL_USER_ID);
        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;

        $this->render('draw.twig');
    }

    protected function saveMailAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->findOrCreate([
            'id' => $this->getId()
        ]);
        $data->set('html' , $_POST['html']);
        $data->save();
    }
}