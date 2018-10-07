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
        $this->setRender('idtopol' , $this->getId() + (100000 * $this->getEntityId()) );
        $this->setRender('userId' , TOPOL_USER_ID);
        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;

        $rst = \DB::for_module( $this->getEntityName() )
            ->select('mod_newslettermodel_id')
            ->select('mod_newslettermodel_name')
            ->where_equal('mod_newslettermodel_element_module_parent_id' , end($this->getIdParent() ) )
            ->where_not_equal('mod_newslettermodel_id' , $this->getId())
            ->find_many();

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab['Newsletter'][ $this->getEntityId() . "-" . $row->mod_newslettermodel_id ] = $row->mod_newslettermodel_name ;
            }
        }

        $Model = $this->Container()->module('EdEmail')->getController(true);
        $top = $Model->getElementForAssociation(NULL, "array");
        if ( $top )
        {
            foreach( $top as $key => $value )
            {
                $tab['Modèle'][ $Model->getEntityId() . "-" . $key ] = $value ;
            }
        }

        $this->setRender( 'select' , $tab );


        $this->render('draw.twig');
    }

    protected function duplicateAction()
    {
        list( $entity , $id ) = explode( '-' , $_POST['template'] );

        if ( $entity == $this->getEntityId() )
        {
            $data = new Data( $this->getEntityName() );
        }
        else
        {
            $data = new Data('EdEmail');
        }

        $data->find([
            'id' => $id
        ]);

        echo $data->get('json');
    }

    protected function saveMailAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->findOrCreate([
            'id' => $this->getId()
        ]);
        $data->set('html' , $_POST['html']);
        $res = (file_get_contents('php://input'));
        list(, $json ) = explode( '&json=' , $res );
        $data->set('json' , $json);
        $data->save();
    }
}