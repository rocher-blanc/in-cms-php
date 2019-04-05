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

    protected function resendAction()
    {
        $EL = new Easyletter;
        $EL->newsletter( $this->getId() );

        $this->Factory()->Response()->flashAndRedirect( "La nouvelle demande de campagne est en cours ..." , true , $this->Factory()->Url()->route( $this->getEntityName() , 'index' , $this->getUriParent() ) );
    }
}