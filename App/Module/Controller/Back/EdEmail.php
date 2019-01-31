<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;
use App\Api\Easyletter;

class EdEmail extends Controller
{
    protected function drawAction()
    {
        $this->setRender('id' , $this->getId() );
        $this->setRender('idtopol' , $this->getId() + ( 100000 * $this->getEntityId() ) );
        $this->setRender('apiKey' , TOPOL_API_KEY);
        $this->setRender('userId' , TOPOL_USER_ID);
        $this->setRender('uri_id_parent' , $this->getUriParent() ) ;

        $select = $this->getElementForAssociation(NULL, "array");

        $this->setRender( 'select' , $select ) ;

        $this->render('draw.twig');
    }

    protected function viewAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->find( $this->getId() );

        $this->setRender( 'html' , $data->get('html') );

        $this->render('view.twig');
    }

    protected function duplicateAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->find([
            'id' => $_POST['template']
        ]);
        echo $data->get('json');
    }

    protected function sendMailAction()
    {
        $EL = new Easyletter;
        $EL->test( $_POST['email'] , $this->getId() , $this->getEntityName() );
    }

    protected function saveMailAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->findOrCreate([
            'id' => $this->getId()
        ]);

        $res = (file_get_contents('php://input'));
        list(, $htmljson ) = explode( '&html=' , $res );
        list($html,$json) = explode( '&json=' , $htmljson );
        $data->set('json' , $json);
        $data->set('html' , $html);
        $data->save();
    }
}