<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;
use App\Api\Easyletter;

class EdAutomation extends Controller
{
    protected function drawAction()
    {
        $this->setRender('id' , $this->getId() );
        $this->setRender('idtopol' , $this->getId() + (100000 * $this->getEntityId()) );
        $this->setRender('apiKey' , TOPOL_API_KEY);
        $this->setRender('userId' , TOPOL_USER_ID);
        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;

        $rst = \DB::for_module( $this->getEntityName() )
            ->select('mod_edautomation_id')
            ->select('mod_edautomation_name')
            ->where_equal('mod_edautomation_element_module_parent_id' , end($this->getIdParent() ) )
            ->where_not_equal('mod_edautomation_id' , $this->getId())
            ->find_many();

        $content = \DB::for_module_assoc( "EdAutomationModel" , 'vars' )
            ->select( \DB::getTableNameAssocValue( "EdAutomationModel" , 'vars' ) )
            ->where_equal( \DB::getTableNameAssoc( "EdAutomationModel" , 'vars' ) . '_' . \DB::getIdName( "EdAutomationModel" ) , end($this->getIdParent() ) )
            ->find_many();

        $result = [] ;

        if ( $content )
        {
            foreach( $content as $row )
            {
                $id = $row->get( \DB::getTableNameAssocValue( "EdAutomationModel" , 'vars' ) ) ;

                $group = new Data('EdAutomationVarGroup');
                $var = new Data('EdAutomationVar');

                $var->find([
                    'id' => $id
                ]);

                $group->find([
                    'id' => $var->get('element_module_parent_id')
                ]);

                $result[ $group->get('name') ][] = [
                    'text' => $var->get('text'),
                    'value' => $var->get('key'),
                    'label' => $var->get('label')
                ];
            }
        }

        $tags = [];
        foreach( $result as $group => $array )
        {
            $obj = new \stdClass;
            $obj->name = $group ;
            $obj->items = [] ;

            foreach( $array as $val )
            {
                $std = new \stdClass;
                $std->value = "[" . $val['value'] . "]";
                $std->text = $val['text'];
                $std->label = $val['label'];
                $obj->items[] = $std ;
            }

            $tags[] = $obj ;
        }

        $this->setRender( 'mergeTags' , json_encode( $tags ) );
/*
        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab['Automotion'][ $this->getEntityId() . "-" . $row->mod_edautomation_id ] = $row->mod_edautomation_name ;
            }
        }*/

        $select = $this->getElementForAssociation(NULL, "array");
        if ( $select )
        {
            foreach( $select as $key => $value )
            {
                $tab['Automotion'][ $this->getEntityId() . "-" . $key ] = $value ;
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

    protected function viewAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->find( $this->getId() );

        $this->setRender( 'html' , $data->get('html') );

        $this->render('view.twig');
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