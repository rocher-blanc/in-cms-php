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
                    ->select( "mod_edautomationhistory_id" )
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
}