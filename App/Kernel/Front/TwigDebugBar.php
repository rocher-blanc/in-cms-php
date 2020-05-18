<?php

namespace App\Kernel\Front;

class TwigDebugBar
{

	protected $var = [];
	protected static $instance = NULL ;

	
	public static function getInstance()
	{
		if( self::$instance == NULL )
		{
			self::$instance = new TwigDebugBar();
		}
		return self::$instance;
	}

	public function addVar( $name , $value )
	{
		$this->var[$name] = $value;
	}

	public function merge( $values )
	{
		$this->var = array_merge( $this->var , $values );
	}

	protected function getDebugTwigVariableInfo( $name , $v ) {
		switch( gettype($v) )
		{
			case "boolean" :
				return [
					'name'  => $name,
					'type'   => "bool",
					'value'  => $v ? '<span style="color:green;">true</span>' : '<span style="color:red;">false</span>'
				];
				break;

			case "integer" :
				return [
					'name'  => $name,
					'type'   => "int",
					'value' => $v
				];
				break;

			case "double" :
				return [
					'name'  => $name,
					'type'  => "float",
					'value' => $v
				];
				break;

			case "string" :
				return [
					'name'  => $name,
					'type'  => "string",
					'value' => strlen($v) > 150 ? substr($v, 0, 150) . "..." : $v
				];
				break;

			case "array" :
				return [
					'name'  => $name,
					'type'  => "array",
					'value' => count($v)
				];
				break;

			case "object" :
				return [
					'name'  => $name,
					'type'  => "object",
					'value' => ""
				];
				break;

			case "NULL" :
				return [
					'name'  => $name,
					'type'  => "null",
					'value' => ""
				];
				break;

			default :
				return [
					'name'  => $name,
					'type'  => "other",
					'value' => ""
				];
				break;
		}
	}

	protected function getDebugTwigExcepts()
	{
		return [ "others" , "user" , "user_error" , "site" , "lang" , "rgpd" , "og" , "md" , "webmaster_tools" , "analytics" , "meta" , "tracking" ];
	}

	public function getDebugTwig()
	{
		$data    = [];
		$tabs    = [ "others" , "user" , "user_error" , "custom" , "site" , "lang" ];
		$excepts = $this->getDebugTwigExcepts();

		foreach( $tabs as $tab ) {

			switch( $tab ) {

				case "others" :
					$data['others'] = [];
					foreach( $this->var as $k => $v )
					{
						if( ! in_array( $k , $excepts ) )
						{
							$data['others'][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
						}
					}
					break;

				case "custom" :
					break;

				default:
					$data[$tab] = [];
					if( array_key_exists( $tab , $this->var ) )
					{
						foreach( $this->var[$tab] as $k => $v )
						{
							$data[ $tab ][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
						}
					}
					break;
			}
		}

		return $data;
	}
}