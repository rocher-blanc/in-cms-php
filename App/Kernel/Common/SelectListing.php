<?php

namespace App\Kernel\Common;

use App\Kernel\Lang;

class SelectListing
{

	public static function findAllByKey( $key )
	{
		$req = self::prepareRequest( $key )
			->find_many();

		return self::parseAll( $req );
	}

	public static function findAllWithMultiLangByKey( $key )
	{
		$lang = Lang::getInstance()->getAll();
		$rst  = [];

		$req = self::prepareRequest( $key )
			->find_many();

		foreach( $req as $row )
		{
			$id = $row->get( self::getColumn( 'id' ) );
			if( ! array_key_exists( $id , $rst ) )
			{
				$rst[ $id ] = [];
				foreach( $lang as $l )
				{
					$rst[ $id ][ $l->id ] = NULL;
				}
			}

			$rst[ $id ][ $row->get( self::getColumn( 'lang_id' , true ) ) ] = self::parse( $row );
		}

		return $rst;
	}


	private static function prepareRequest( $key )
	{
		return \DB::for_table( 'select_listing' )
			->join( 'select_listing_lang' , [ self::getColumn('select_listing_id', true ) , '=' , self::getColumn('id') ] )
			->where_equal( self::getColumn('key') , $key )
			->order_by_asc( self::getColumn('order') );
	}

	private static function getColumn( $name , $lang = false )
	{
		return "select_listing_" . ( $lang ? 'lang_' : '' ) . $name ;
	}

	private static function parseAll( $rows )
	{
		$rst = [];
		if( $rows )
		{
			foreach( $rows as $row )
			{
				$rst[ $row->get( self::getColumn('id') ) ] = self::parse( $row );
			}
		}

		return $rst;
	}

	private static function parse( $row )
	{
		if( $row )
		{
			return $row->get( self::getColumn('value', true) );
		}
		else
		{
			return NULL;
		}
	}

}