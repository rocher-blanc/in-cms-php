<?php

namespace App\Kernel\Back;

class Menu
{
	/*
	 * @array
	 * Contient toute la decoupe de l'URL
	 * On l'obtient grace au Router de App
	 */
	private $_url = [] ;
	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct() {}
	
	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */
	
	private function getApp()
	{
		return \Slim\Slim::getInstance() ;
	}
	
	private function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}
	
	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */
	
	public function load()
	{
		$this->_url = $this->Factory()->Url()->cutUrl() ;
		
		$rst = \DB::for_table('module_group')
			->where_equal('module_group_active',1)
			->order_by_asc('module_group_order')
			->find_many();
		
		$rowsArray = [];
		if ( $rst )
		{
			foreach( $rst as $row )
			{
				$std = new \stdClass;
				$std->icon = $row->module_group_icon ;
				$std->name = $row->module_group_name ;
				$std->open = false ;
				
				$childs = \DB::for_table('module')
							->where(array('module_active' => 1,'module_module_group_id' => $row->module_group_id))
							->order_by_asc('module_order')
							->find_many() ;
				
				$std->children = [] ;
				if ( $childs ) 
				{
					foreach( $childs as $child )
					{
						$Guard = new \App\Kernel\Back\Acl;
						$Guard->setModule( $child->module_class_name );
						$Guard->load();
						
						if ( $Guard->hasRight() == true )
						{
							$obj = new \stdClass;
							$obj->name  	= $child->module_name ;
							$obj->class 	= $child->module_class_name ;
							$obj->icon 		= $child->module_icon ;
							
							if ( array_key_exists( 0 , $this->_url ) == true && array_key_exists( 1 , $this->_url ) == true )
							{
								$obj->selected = ( $this->_url[0] == 'module' && $this->_url[1] == $obj->class ? true : false ) ;
							}
							else
							{
								$obj->selected = false ;
							}
							
							if ( $obj->selected == true ) $std->open = true ;
							
							$std->children[] = $obj ;
						}
					}
					if ( !empty( $std->children ) ) $rowsArray[] = $std ;
				}
			}
		}

        $this->getApp()->view()->appendData([
            'adminFolder'       => trim( $this->getApp()->config('admin.url') , "/"),
            'menu'              => ( array_key_exists( 1 , $this->_url ) == true ? $this->_url[1] : '' ),
			'menuTree'          => $rowsArray,
			'active_user'       => ACTIVE_USER,
			'active_newsletter' => NEWSLETTER_ACTIVE,
			'color'             => COLOR,
			'techno'            => TECHNO
        ]);
	}
}