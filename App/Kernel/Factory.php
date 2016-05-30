<?php

namespace App\Kernel;

class Factory
{
	private $arrayFactory = array() ;
	private static $instance = NULL ;
	
	public function __call( $method, $arguments )
	{
		return $this->newFactory( $method ) ;
	}

	public static function getInstance()
	{

		return self::$instance ;
	}
	
	private function newFactory( $name )
	{
		if ( ! array_key_exists( $name , $this->arrayFactory ) )
		{
			$fileName = FACTORY_PATH . "/" . $name . ".php" ;
			if ( ! file_exists( $fileName ) ) $this->Response()->error('Impossible de créer la Factory "' . $name . '" car le fichier n\'éxiste pas') ;
			
			$className = "App\Kernel\Factory\\$name" ;
			$this->arrayFactory[ $name ] = new $className ;
		}
		
		return $this->arrayFactory[ $name ] ;
	}
}