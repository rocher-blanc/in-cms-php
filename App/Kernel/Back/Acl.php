<?php

namespace App\Kernel\Back;

class Acl
{
	/* Contient le nom de l'extension a verifier */
	private $_extension = "" ;
	
	/* Contient l'ID du module a verifier */
	private $_module = NULL ;
	
	/* Contient l'ID du groupe a verifier */
	private $_group_id = NULL ;
	
	/* Contient le binaire des droits */
	private $_binary = "" ;
	
	/* Contient le décimale des droits */
	private $_dec = NULL ;
	
	/* Nombres de bites pour les droits */
	private $_max_rigth = 5 ;
	
	/* Charge les variables contenant les permissions */
	private $_add    	 = false ;
	private $_update 	 = false ;
	private $_delete 	 = false ;
	private $_validation = false ;
	private $_config 	 = false ;
	
	public function __construct()
    {
		if ( isset( $_SESSION[ \App\Kernel\Config::getInstance()->get('session') ] ) )
        {
            $this->setGroupId( $_SESSION[ \App\Kernel\Config::getInstance()->get('session') ]['group_id'] ) ;
        }
	}
	
	/* ************** SETTER **************** */
	
	public function setExtension( $var )
	{
		$this->_extension = $var ;
		
		return $this ;
	}
	
	public function setModule( $var )
	{
		$this->_module = $var ;
		
		return $this ;
	}
	
	public function setGroupId( $var )
	{
		$this->_group_id = $var ;
		
		return $this ;
	}
	
	/* ************** GETTER **************** */
	
	private function getExtension()
	{
		return $this->_extension ;
	}
	
	private function getExtensionId()
	{
		$extension = \DB::for_table('extension')
				->select('extension_id')
				->where( array( "extension_technical_name" => $this->getExtension() ) )
				->find_one();
		
		return $extension->extension_id ;
	}
	
	private function getModuleId()
	{
		$module = \DB::for_table('module')
				->select('module_id')
				->where( array( "module_class_name" => $this->getModule() ) )
				->find_one();
		
		return $module->module_id ;
	}
	
	private function getModule()
	{
		return $this->_module ;
	}
	
	private function getGroupId()
	{
		return $this->_group_id ;
	}
	
	private function getApp()
	{
		return \App\Kernel\Config::getInstance() ; // migrated from SlimBridge
	}
	
	private function getBinary()
	{
		$this->_bin  = ( $this->_add == true ? "1" : "0" ) ;
		$this->_bin .= ( $this->_update == true ? "1" : "0" ) ;
		$this->_bin .= ( $this->_delete == true ? "1" : "0" ) ;
		$this->_bin .= ( $this->_validation == true ? "1" : "0" ) ;
		$this->_bin .= ( $this->_config == true ? "1" : "0" ) ;
		
		return $this->_bin ;
	}
	
	/* ************** UPDATER **************** */
	
	public function updateAdd()
	{
		$this->_add = ( $this->_add == true ? false : true ) ;
		$this->update() ;
	}
	
	public function updateUpdate()
	{
		$this->_update = ( $this->_update == true ? false : true ) ;
		$this->update() ;
	}
	
	public function updateDelete()
	{
		$this->_delete = ( $this->_delete == true ? false : true ) ;
		$this->update() ;
	}
	
	public function updateValidation()
	{
		$this->_validation = ( $this->_validation == true ? false : true ) ;
		$this->update() ;
	}
	
	public function updateConfig()
	{
		$this->_config = ( $this->_config == true ? false : true ) ;
		$this->update() ;
	}
	
	private function update()
	{
		if ( $this->getModule() === NULL && $this->getExtension() != '' ) {
			$clef = "permission_extension_id" ;
			$val  = $this->getExtensionId() ;
		}
		else if ( $this->getModule() !== NULL && $this->getExtension() == '' ) {
			$clef = "permission_module_id" ;
			$val  = $this->getModuleId() ;
		}
		
		$perm = \DB::for_table('permission')
			->where( array( $clef => $val , "permission_group_id" => $this->getGroupId() ) )
			->find_one();
		
		if ( ! $perm ) {
			$perm = \DB::for_table('permission')->create();
		}
		else if ( $this->hasRight() == false ) {
			$perm->delete() ;
			
			return true ;
		}
		
		$perm->set( $clef , $val ) ;
		$perm->set( "permission_group_id" , $this->getGroupId() ) ;
		$perm->set( "permission_value" , $this->getBinary() ) ;
		$perm->save();
	}
	
	/* ************** FUNCTIONS **************** */
	
	public function load()
	{
		if ( $this->isAdmin() == true )
		{
			$this->freeAccess() ;
			return true;
		}
		
		if ( $this->getModule() === NULL && $this->getExtension() != '' ) {
			$clef = "permission_extension_id" ;
			$val  = $this->getExtensionId() ;
		}
		else if ( $this->getModule() !== NULL && $this->getExtension() == '' ) {
			$clef = "permission_module_id" ;
			$val  = $this->getModuleId() ;
		}
		
		$perm = \DB::for_table('permission')
			->select('permission_value')
			->where( array( $clef => $val , "permission_group_id" => $this->getGroupId() ) )
			->find_one();
		
		if ( $perm ) $this->convertValue( $perm->permission_value ) ;
		else		 $this->noAccess() ;
		
		return $this ;
	}
	
	public function isAdmin()
	{
		return ( $this->getGroupId() == 1 ? true : false ) ;
	}
	
	public function checkAdd()
	{
		return $this->_add ;
	}
	
	public function checkUpdate()
	{
		return $this->_update ;
	}
	
	public function checkDelete()
	{
		return $this->_delete ;
	}
	
	public function checkValidation()
	{
		return $this->_validation ;
	}
	
	public function checkConfig()
	{
		return $this->_config ;
	}
	
	private function convertValue( $bin )
	{
		while( strlen( $bin ) != $this->_max_rigth )
		{
			$bin = "0" . $bin ;
		}
		
		$this->_add    		= $this->convertToBoolean( substr( $bin , 0, 1) ) ;
		$this->_update 		= $this->convertToBoolean( substr( $bin , 1, 1) ) ;
		$this->_delete 		= $this->convertToBoolean( substr( $bin , 2, 1) ) ;
		$this->_validation  = $this->convertToBoolean( substr( $bin , 3, 1) ) ;
		$this->_config 		= $this->convertToBoolean( substr( $bin , 4, 1) ) ;
	}
	
	private function freeAccess()
	{
		$this->_add   		= true ;
		$this->_update 		= true ;
		$this->_delete 		= true ;
		$this->_validation 	= true ;
		$this->_config 		= true ;
	}
	
	private function noAccess()
	{
		$this->_add   		= false ;
		$this->_update 		= false ;
		$this->_delete 		= false ;
		$this->_validation 	= false ;
		$this->_config 		= false ;
	}
	
	public function hasRight()
	{
		if ( $this->_add or $this->_update or $this->_delete or $this->_validation or $this->_config )  return true ;
		else																							return false ;
	}
	
	private function convertToBoolean( $value )
	{
		if ( $value == "1" ) return true ;
		else				 return false ;
	}
}