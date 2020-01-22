<?php

namespace App\Kernel;

class Database
{
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
		
		\DB::configure('mysql:host=' . DB_HOST . ( defined('DB_PORT') ? ';port=' . DB_PORT : '' ) . ';dbname=' . DB_DATABASE );
		\DB::configure('username', DB_USER );
		\DB::configure('password', DB_PASSWORD );
		\DB::configure('driver_options', [
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		]);
		
		if ( DEBUG_CMS OR DEBUG_BAR )
        {
            \DB::configure('logging', true);
            \DB::configure('logger', function($bound_query, $query_time) {
                \App\Kernel\Debug::logORM( $bound_query , $query_time ) ;
            });
        }

        \DB::for_table('')->raw_query("SELECT 1")->find_one();
	}

    public function caching()
    {
        if ( ! DEBUG_CMS ) \DB::configure('caching', true);
    }

    public function testTable()
    {
        if ( DEBUG_CMS )
        {
            \DB::for_table('param')->limit(1)->find_one();
        }

        defined('DB_CONNECT') || define('DB_CONNECT', true );
    }
}