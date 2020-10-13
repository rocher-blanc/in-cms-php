<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;
use App\Api\Easyletter;
use App\Kernel\Front\Translate;

class NewsletterCampaign extends Controller
{
    protected function filterTable( $content )
    {
        $tab = [];
        foreach( $content as $row )
        {
            if ( ! empty( $row->get( $this->getEntity()->get('id_easyletter')->getColumn() ) ) )
            {
                $tab[] = $row->get( $this->getEntity()->get('id_easyletter')->getColumn() ) ;
            }
        }

        sort( $tab , SORT_NUMERIC );

        $el = new Easyletter();

        $this->resultStats = $el->stats( $tab );

        return $content ;
    }

    protected function filterContent( $content )
    {
        if ( $content )
        {
            $content->stats = $this->resultStats[ $content->id_easyletter ] ;
        }

        return $content ;
    }

    public function hookAddSaveAfter( $c )
    {
        $data = new Data('NewsletterCampaign');
        $data->find( $this->getId() );

        if ( $data->get('type') == NULL )
        {
            $data->set('type' , 1);
            $data->save();
        }

        $EL = new Easyletter;
        $EL->newsletter( $this->getId() );
    }

    public function hookAddCheckAfter()
    {
        $date = $this->post( $this->getEntity()->get('date')->getColumn() );
        list( $date , $hor ) = explode( " - " , $date );
        list( $d,$m,$y ) = explode( '/' , $date );
        list( $h,$i ) = explode( ":" , $hor );

        $datetime = new \DateTime("$y-$m-$d $h:$i:00");

        if ( $datetime->format('U') < time() )
        {
            return [
                'msg' => Translate::getInstance()->getText('mandatory_date_programming'),
                "result" => false
            ] ;
        }
        else
        {
            return true ;
        }
    }

    protected function hookDeleteBefore()
    {
        $data = new Data('NewsletterCampaign');
        $data->find( $this->getId() );

        if ( $data->get('id_easyletter') != '' )
        {
            $EL = new Easyletter;
            $EL->cancel( $data->get('id_easyletter') );

            return true;
        }

        return true ;
    }

    protected function statsAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->find([
            'id' => $this->getId()
        ]);

        $grps = [];
        foreach( $data->get('recipient') as $recpt )
        {
            $rec = new Data('NewsletterGroup');
            $recrst = $rec->find( $recpt );
            if ( $recrst ) $grps[ $recpt ] = $rec->get('name');
        }

        $el = new Easyletter();
        $tabStats = $el->stats( $data->get('id_easyletter') );

        $this->setRender('statistics' , $tabStats );
        $this->setRender('id' , $data->get('id') );
        $this->setRender('rst' , [
            'subject' => $data->get('subject'),
            'destinataires' => $grps
        ] );

        $file = 'stats.twig' ;
        if ( $data->get('version') == 'v3' )
        {
            $file = 'stats_v3.twig' ;
        }

        $this->render($file);
    }

    protected function cancelAction()
    {
        $data = new Data('NewsletterCampaign');
        $rst = $data->find( $this->getId() );

        if ( $rst == true )
        {
            $EL = new Easyletter;
            $EL->cancel( $data->get('id_easyletter') );

            $this->Factory()->Response()->printJSON( ['msg' => 'La campagne est en cours d\'annulation', 'result' => true, 'url' => $this->Factory()->Url()->route( $this->getEntityName() , "index" , $this->getUriParent() ) ] );
        }
        else
        {
            $this->Factory()->Response()->printJSON( ['msg' => 'Campagne introuvale', 'result' => false, 'url' => $this->Factory()->Url()->route( $this->getEntityName() , "index" , $this->getUriParent() ) ] );
        }
    }

    protected function resend( $type )
    {
        $rst = $this->duplicate();

        if ( $rst['result'] == true )
        {
            $data = \DB::for_module( $this->getEntityName() )->where_id_is( $rst['id'] )->find_one();
            if ( $data )
            {
                $one = $this->getRepository()->findOne( $this->getId() );

                $data->mod_newslettercampaign_subject = $one->mod_newslettercampaign_subject ;
                $data->mod_newslettercampaign_type = $type ;
                $data->mod_newslettercampaign_id_easyletter = NULL ;
                $data->mod_newslettercampaign_newsletter_parent = $this->getId() ;
                $data->save();

                $EL = new Easyletter;
                $EL->newsletter( $rst['id'] );

                $this->Factory()->Response()->printJSON( ['msg' => 'La campagne est en cours de programmation', 'result' => true, 'url' => $this->Factory()->Url()->route( $this->getEntityName() , "index" , $this->getUriParent() ) ] );
            }
            else
            {
                $this->Factory()->Response()->printJSON( $rst );
            }
        }
        else
        {
            $this->Factory()->Response()->printJSON( $rst );
        }
    }

    protected function resendNoClickAction()
    {
        return $this->resend( 3 );
    }

    protected function resendNoReadAction()
    {
        return $this->resend( 2 );
    }

    protected function resendNoReadViewAction()
    {
        $this->setRender( 'id' , $this->getId() ) ;
        $this->render('sendNoRead.twig') ;
    }

    protected function resendNoClickViewAction()
    {
        $this->setRender( 'id' , $this->getId() ) ;
        $this->render('sendNoClick.twig') ;
    }

    protected function downloadStatisticAction()
    {
        $one = $this->getRepository()->findOne( $this->getId() );

        if ( $one )
        {
            $EL = new Easyletter;
            $rst = $EL->downloadStats( $one->mod_newslettercampaign_id_easyletter , 'pdf' );

            if ( is_array( $rst ) || $rst === false )
            {
                die('error');
            }
            else
            {
                $this->getApp()->contentType('application/pdf');
                $this->getApp()->response()->headers->set('Content-Disposition', "attachment;filename=statistics_newsletter_".$this->getId().".pdf");
                $this->getApp()->response()->body( $rst );
            }
        }
        else
        {
            die('error');
        }
    }

    protected function downloadStatisticDestAction()
    {
        $one = $this->getRepository()->findOne( $this->getId() );

        if ( $one )
        {
            $EL = new Easyletter;
            $rst = $EL->downloadStats( $one->mod_newslettercampaign_id_easyletter , 'excel' );

            if ( is_array( $rst ) || $rst === false )
            {
                die('error');
            }
            else
            {
                $this->getApp()->response()->headers->set('Content-Disposition', "attachment;filename=statistics_newsletter_".$this->getId().".xls");
                $this->getApp()->response()->headers->set('Cache-Control', "must-revalidate, post-check=0, pre-check=0");
                $this->getApp()->response()->headers->set('Expires', "0");
                $this->getApp()->response()->headers->set('Content-Type', "application/vnd.ms-excel; charset=utf-8");
                $this->getApp()->response()->body( $rst );
            }
        }
        else
        {
            die('error');
        }
    }
}