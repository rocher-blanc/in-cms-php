<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;

class EdAutomation extends Controller
{
    protected function drawAction()
    {
        $this->setRender('id' , $this->getId() );
        $this->setRender('idtopol' , $this->getId() + 100000 );
        $this->setRender('apiKey' , TOPOL_API_KEY);
        $this->setRender('userId' , TOPOL_USER_ID);
        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;

        $content = $this->getRepository()->findOne( $this->getId() );

        $json = str_replace('\"' , '"' , $content->get( $this->getEntity()->get('json')->getColumn() ) );
        $json = str_replace('"' , '\"' , $json );
        $this->setRender( 'json' , $json ) ;

        $this->render('draw.twig');
    }

    protected function saveMailAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->findOrCreate([
            'id' => $this->getId()
        ]);

        $data->set('html' , $_POST['html']);
        $data->set('json' , $_POST['json']);
        $data->save();
    }
}