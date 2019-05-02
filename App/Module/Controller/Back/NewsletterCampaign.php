<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;
use App\Api\Easyletter;

class NewsletterCampaign extends Controller
{
    protected function filterTable( $content )
    {
        $tab = [];
        foreach( $content as $row )
        {
            $tab[] = $row->get( $this->getEntity()->get('id_easyletter')->getColumn() ) ;
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
                'msg' => "La date de programmation ne doit pas être inférieur à aujourd'hui",
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
            return (bool) $EL->cancel( $data->get('id_easyletter') );
        }

        return false ;
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
            if ( $recrst ) $grps[] = $rec->get('name');
        }

        $el = new Easyletter();
        $tabStats = $el->stats( $data->get('id_easyletter') );

        $this->setRender('id' , $data->get('id') );
        $this->setRender('rst' , [
            'subject' => $data->get('subject'),
            'destinataires' => $grps
        ] );
        $links = $tabStats['links']['records'];
        for( $j = 0; $j <= count($links); $j++ )
        {
            $tabLinks[] = [
                "clicCount" => $links[$j][2],
                "link" => $links[$j][1],
                "recipientsClic" => $links[$j][3]
            ];
        }
        $state = $tabStats['routage']['records'][0][1];
        $this->setRender('res' , [
            'ToSend' => $tabStats['routage']['records'][0][2],
            "Sent" => $tabStats['routage']['records'][0][3],
            "HardBounces" => $tabStats['routage']['records'][0][8],
            "RecipientsRead" => $tabStats['routage']['records'][0][4],
            "RecipientsClic" => $tabStats['routage']['records'][0][5],
            "SoftBounces" => $tabStats['routage']['records'][0][7],
            "Unsubscribe" => $tabStats['routage']['records'][0][6],
            "State" => $tabStats['routage']['records'][0][1],
            "StateStr" => $tabStats['state_routage'][$state],
        ] );

        $records = $tabStats['destStats']['records'];
        for( $i = 0; $i < count($records); $i++ )
        {
            $tabDests[] = [
                "recipientId" => $records[$i][0],
                "email" => $records[$i][1],
                "MobilePhone" => $records[$i][2],
                "state" => $records[$i][3],
                "read" => $records[$i][4],
                "readDateUTC" => $records[$i][5],
                "unsubscribe" => $records[$i][6],
                "unsubscribeDateUTC" => $records[$i][7],
                "vacation" => $records[$i][8],
                "clicCount" => $records[$i][9],
                "linkClicCount" => $records[$i][10]
            ];
        }

        $this->setRender('dests' , $tabDests );
        $this->setRender('status' , $tabStats['state_mailing']);
        $this->setRender('links' , $tabLinks );

        $this->render('stats.twig');
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