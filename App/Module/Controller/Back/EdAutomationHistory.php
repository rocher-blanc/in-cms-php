<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;
use App\Api\Easyletter;

class EdAutomationHistory extends Controller
{
    protected function filterTable( $content )
    {
    	if( defined('EL_TOKEN') )
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
		else
		{
			return $content;
		}
    }

    protected function filterContent( $content )
    {
    	if( defined('EL_TOKEN') )
		{
			if ( $content )
			{
				$content->stats = $this->resultStats[ $content->id_easyletter ] ;
                $req = \DB::for_table( "mod_edautomationhistory" )
                    ->select( "mod_edautomationhistory_information" )
                    ->where_equal( "mod_edautomationhistory_id_easyletter" , $content->id_easyletter )
                    ->find_one();

                if ( $req )
                {
                    $req->mod_edautomationhistory_information = null;
                    $req->save();
                }
			}

			return $content ;
		}
		else
		{
			return $content ;
		}

    }

    protected function statsAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->find([
            'id' => $this->getId()
        ]);

        $el = new Easyletter();
        $tabStats = $el->stats( $data->get('id_easyletter') );

        $this->setRender('id' , $data->get('id') );
        $this->setRender('rst' , [
            'subject' => "",
            'destinataires' => $data->get('email')
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
}