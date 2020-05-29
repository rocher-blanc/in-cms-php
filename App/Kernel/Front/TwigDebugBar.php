<?php

namespace App\Kernel\Front;

use App\Kernel\Factory;

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
					'name'    => $name,
					'type'    => "boolean",
					'class'   => "boolean-" . ( $v ? 'true' : 'false' ),
					'is_link' => false,
					'value'   => $v ? 'true' : 'false'
				];
				break;

			case "integer" :
				return [
					'name'    => $name,
					'type'    => "int",
					'class'   => "integer",
					'is_link' => false,
					'value'   => $v
				];
				break;

			case "double" :
				return [
					'name'    => $name,
					'type'    => "float",
					'class'   => "integer",
					'is_link' => false,
					'value'   => $v
				];
				break;

			case "string" :
				return [
					'name'    => $name,
					'type'    => "string",
					'class'   => "string",
					'is_link' => false,
					'value'   => strlen($v) > 800 ? substr($v, 0, 800) . "..." : $v
				];
				break;

			case "array" :
				return [
					'name'    => $name,
					'type'    => "array",
					'class'   => "integer",
					'is_link' => false,
					'value'   => count($v)
				];
				break;

			case "object" :
				return [
					'name'    => $name,
					'type'    => "object",
					'class'   => "integer",
					'is_link' => false,
					'value'   => ""
				];
				break;

			case "NULL" :
				return [
					'name'    => $name,
					'type'    => "null",
					'class'   => "boolean-false",
					'is_link' => false,
					'value'   => ""
				];
				break;

			default :
				return [
					'name'    => $name,
					'type'    => "undefined",
					'class'   => "integer",
					'is_link' => false,
					'value'   => ""
				];
				break;
		}
	}

	protected function getDebugTwigExcepts()
	{
		return [ "current page vars" , "element" , "arrayGetAll" , "pagination" , "shop_cart" , "user" , "user_error" , "site" , "lang" , "rgpd" , "og" , "md" , "webmaster_tools" , "analytics" , "meta" , "tracking" ];
	}

	public function getDebugTwig()
	{
		$data    = [];
		$tabs    = [ "current page vars" , "element" , "listing" , "pagination" , "cart" , "user" , "user_error" , "custom" , "site" , "lang" , "pages" , "modules" ];
		$excepts = $this->getDebugTwigExcepts();

		foreach( $tabs as $tab ) {

			switch( $tab ) {

				case "current page vars" :
					$data['current page vars'] = [];
					foreach( $this->var as $k => $v )
					{
						if( ! in_array( $k , $excepts ) )
						{
							$data['current page vars'][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
						}
					}
					break;

				case "element":
					if( array_key_exists("element" , $this->var ) ) {
						foreach( $this->var['element'] as $k => $v )
						{
							$data['element'][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
						}
					}
					break;

				case "listing":
					if( array_key_exists("arrayGetAll" , $this->var ) ) {
						$data['listing []'] = [];
						$data['listing []']['total'] = [
							'name'    => "Total elements",
							'type'    => "integer",
							'class'   => "integer",
							'value'   => count($this->var['arrayGetAll'])
						];
						if( count($this->var['arrayGetAll']) > 0 )
						{
							foreach( $this->var['arrayGetAll'][0] as $k => $v )
							{
								$data['listing []'][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
							}
						}
					}
					break;

				case "pagination":
					if( array_key_exists("pagination" , $this->var ) ) {
						foreach( $this->var['pagination'] as $k => $v )
						{
							$data['pagination'][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
						}
					}
					break;

				case "cart":
					if( array_key_exists("shop_cart" , $this->var ) ) {
						foreach( $this->var['shop_cart'] as $k => $v )
						{
							$data['cart'][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
						}
					}
					break;

				case "product":
					if( isset($this->var['shop_cart']['products']) ) {
						$data['product []'] = [];
						if( count($this->var['shop_cart']['products']) > 0 )
						{
							foreach( $this->var['shop_cart']['products'][0] as $k => $v )
							{
								$data['product []'][ $k ] = $this->getDebugTwigVariableInfo( $k , $v );
							}
						}
					}
					break;

				case "pages" :
					$data['pages'] = [];
					$req = \DB::for_table( "page" )
						->select( "page_id" )
						->select( "page_name" )
						->where_equal( "page_active" , 1 )
						->find_array();

					foreach( $req as $i ) {
						$data['pages'][] = [
							'name'    => $i['page_name'],
							'type'    => $i['page_id'],
							'class'   => "link",
							'is_link' => true,
							'value'   => '<a href="'. Factory::getInstance()->Url()->page($i['page_id'], true) .'" class="dbg-menu-content-link"></a>'
						];
					}

					break;

				case "modules" :
					$data['modules'] = [];
					$req = \DB::for_table( "module" )
							->select( "module_id" )
							->select( "module_name" )
							->select( "module_class_name" )
							->select( "module_index" )
							->select( "module_index_elmt" )
						    ->where_equal( "module_active" , 1 )
							->where_equal( "module_kernel" , 0 )
							->find_array();

					foreach( $req as $i ) {
						$data['modules'][] = [
							'name'    => $i['module_name'] . ' <em><small>('. $i['module_class_name'] .')</small></em>',
							'type'    => $i['module_id'],
							'class'   => "link",
							'is_link' => true,
							'value'   => $i['module_index']
										? '<a href="'. Factory::getInstance()->Url()->module($i['module_id'], true) .'" class="dbg-menu-content-link"></a>'
										: ''
						];
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