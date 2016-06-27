<?php

namespace App\Kernel;

class Database
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */

	public $isConnect = false ;

	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct() {}
	
	/* ************************************************** */
	/* ****************   FUNCTIONS   ******************* */
	/* ************************************************** */
	
	public function connect()
	{
		require_once KERNEL_PATH . '/DB.php';
		
		\DB::configure('mysql:host=' . DB_HOST . ( defined( DB_PORT ) ? ';port=' . DB_PORT : '' ) . ';dbname=' . DB_DATABASE );
		\DB::configure('username', DB_USER );
		\DB::configure('password', DB_PASSWORD );
		\DB::configure('driver_options', [
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		]);
		
		if ( DEBUG )
        {
            \DB::configure('logging', true);
            \DB::configure('logger', function($bound_query, $query_time) {
                \App\Kernel\Debug::logORM( $bound_query , $query_time ) ;
            });
        }

		$this->isConnect = true ;
		defined('DB_CONNECT') || define('DB_CONNECT', true );
	}

	public function caching()
	{
		if ( ! DEBUG ) \DB::configure('caching', true);
	}

	public function isConnect()
	{
		return $this->isConnect ;
	}


}