<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;

class EdAutomationVarGroup extends Controller
{

	protected function addVariablesAction()
	{
		$keys = $this->getVariables() ;

		if( $this->getApp()->request->isPost() )
		{
			if( $this->post('action') == "add_variables" )
			{
				$this->createVariables( $this->post('content') , $keys );
				$keys = $this->getVariables() ;
			}
		}

		$this->setRender( 'id'   , $this->getId() );
		$this->setRender( 'keys' , $keys          );
		$this->render('addVariables.twig' , [
			'keys' => $keys
		]);
	}

	private function getVariables()
	{
		$rst = [];

		$req = \DB::for_table( "mod_edautomationvar" )
			->select( "mod_edautomationvar_id" )
			->select( "mod_edautomationvar_key" )
			->select( "mod_edautomationvar_text" )
			->where_equal( "mod_edautomationvar_element_module_parent_id" , $this->getId() )
			->find_many();

		if( $req )
		{
			foreach( $req as $row )
			{
				$rst[ $row->mod_edautomationvar_key ] = $row->mod_edautomationvar_text ;
			}
		}

		return $rst;
	}

	private function createVariables( $content , $oldList )
	{
		$content = explode( "\n" , $content );
		foreach( $content as $row )
		{
			$line = explode( ";" , $row );
			if( count($line) == 2 )
			{
				$key  = trim( $line[0] );
				$text = trim( $line[1] );

				if( strlen($key) > 0 && strlen($text) > 0 )
				{
					if( ! array_key_exists( $key , $oldList ) )
					{
						$now = date("Y-m-d H:i:s");
						$prep = \DB::for_table( "mod_edautomationvar" )->create();
						$prep->mod_edautomationvar_date_created             = $now;
						$prep->mod_edautomationvar_date_last_updated        = $now;
						$prep->mod_edautomationvar_date_updated             = $now;
						$prep->mod_edautomationvar_element_module_parent_id = $this->getId();
						$prep->mod_edautomationvar_key                      = $key;
						$prep->mod_edautomationvar_text                     = $text;
						$prep->mod_edautomationvar_label                    = $text;
						$prep->save();
					}
				}
			}
		}
	}

}