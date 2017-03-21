<?php

namespace App\Kernel;

class Redis
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private static $instance = NULL ;
    private $redis = NULL ;
	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct()
    {
        if ( REDIS == true )
        {
            $this->redis = new Redis();
            $this->getRedis()->connect( REDIS_SERVER , REDIS_PORT );

            // $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);	// don't serialize data
            $this->getRedis()->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);	// use built-in serialize/unserialize
            // $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);	// use igBinary serialize/unserialize
        }
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    private function getRedis()
    {
        return $this->redis ;
    }

    private function getPrefix()
    {
        return $_SERVER['HTTP_HOST'] ;
    }

	public static function getInstance()
	{
		if ( self::$instance === NULL ) self::$instance = new Redis;
		
		return self::$instance ;
	}
	
	public function get( $key )
	{
        if ( ! REDIS ) return false ;

        if ( is_array( $key ) )
        {
            $tab = [];

            foreach( $key as $row )
            {
                $tab[ $this->getPrefix() . "-" . $row ] = $this->getPrefix() . "-" . $row ;
            }

            return $this->getRedis()->get( $tab );
        }
        else
        {
            return $this->getRedis()->get( $this->getPrefix() . "-" . $key );
        }
	}

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */

    public function set( $key , $value )
    {
        if ( ! REDIS ) return false ;

        return $this->getRedis()->set( $this->getPrefix() . "-" . $key , $value );
    }

    /* ************************************************** */
    /* ****************     DELETE    ******************* */
    /* ************************************************** */

    public function delete( $key )
    {
        if ( ! REDIS ) return false ;

        if ( is_array( $key ) )
        {
            $tab = [];

            foreach( $key as $row )
            {
                $tab[ $this->getPrefix() . "-" . $row ] = $this->getPrefix() . "-" . $row ;
            }

            return $this->getRedis()->delete( $tab );
        }
        else
        {
            return $this->getRedis()->delete( $this->getPrefix() . "-" . $key );
        }
    }

    /* ************************************************** */
    /* ****************     EXIST     ******************* */
    /* ************************************************** */

    public function exist( $key )
    {
        if ( ! REDIS ) return false ;

        return $this->getRedis()->exist( $_SERVER['HTTP_HOST'] . "-" . $key );
    }
}