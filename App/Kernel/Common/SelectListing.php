<?php

namespace App\Kernel\Common;

use App\Kernel\Lang;

class SelectListing
{

	public static function findAllByKey( $key )
	{
		return self::prepareRequest( $key )
			->find_many();
	}

	public static function findValue( $elementId , $langId )
	{
		return \DB::for_table( 'select_listing_lang' )
			->where_equal( self::getColumn( 'select_listing_id', true ) , $elementId )
			->where_equal( self::getColumn( 'lang_id', true ) , $langId )
			->find_one();
	}

	public static function getAllByKey( $key )
	{
		$req = self::findAllByKey( $key );
		return self::parseAll( $req );
	}

	public static function getAllWithMultiLangByKey( $key )
	{
		$lang = Lang::getInstance()->getAll();
		$rst  = [];

		$req = self::findAllByKey( $key );

		foreach( $req as $row )
		{
			$id = $row->get( self::getColumn( 'id' ) );
			if( ! array_key_exists( $id , $rst ) )
			{
				foreach( $lang as $l )
				{
					$rst[ $id ][ $l->id ] = NULL;
				}
			}

			$rst[ $id ][ $row->get( self::getColumn( 'lang_id' , true ) ) ] = self::parse( $row );
		}

		return $rst;
	}

	public static function removeEntry( $id )
	{
		// Lang
		$req = \DB::for_table('select_listing_lang')
			->where_equal( self::getColumn('select_listing_id', true ) , $id )
			->find_many();
		if( $req )
		{
			foreach( $req as $row )
			{
				$row->delete();
			}
		}

		// Common
		$req = \DB::for_table('select_listing')
			->where_equal( self::getColumn('id' ) , $id )
			->find_one();
		if( $req )
		{
			$req->delete();
		}
	}

	public static function add( $key )
	{
		$prep = \DB::for_table( 'select_listing' )->create();
		$prep->set( self::getColumn( 'key' )   , $key );
		$prep->set( self::getColumn( 'order' ) , 0    );
		$prep->save();
		return $prep->get( self::getColumn('id') );
	}

	public static function addLang( $elementId , $langId , $value )
	{
		$prep = \DB::for_table( 'select_listing_lang' )->create();
		$prep->set( self::getColumn( 'select_listing_id', true )   , $elementId );
		$prep->set( self::getColumn( 'lang_id'          , true )   , $langId    );
		$prep->set( self::getColumn( 'value'            , true )   , $value     );
		$prep->save();
		return $prep->get( self::getColumn('id') );
	}

	public static function getColumn( $name , $lang = false )
	{
		return "select_listing_" . ( $lang ? 'lang_' : '' ) . $name ;
	}

	private static function prepareRequest( $key = NULL )
	{
		$prep = \DB::for_table( 'select_listing' )
			->join( 'select_listing_lang' , [ self::getColumn('select_listing_id', true ) , '=' , self::getColumn('id') ] )
			->order_by_asc( self::getColumn('order') );

		if( $key != NULL )
		{
			$prep->where_equal( self::getColumn('key') , $key );
		}

		return $prep;
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