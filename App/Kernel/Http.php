<?php

namespace App\Kernel;

class Http
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private static $instance = NULL ;
	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct()
	{
		if ( FILE_CONFIG !== false && DB_CONNECT === true ) $this->load() ;
	}
	
	/* ************************************************** */
	/* ****************    SETTER     ******************* */
	/* ************************************************** */ 
	
	
	
	/* ************************************************** */
	/* ****************     GETTER    ******************* */
	/* ************************************************** */

	public function getUrl()
	{
		return $this->CMS()->getApp()->request()->getUrl() ;
	}

    public function getCdn()
	{
		return ( $this->CMS()->isDev() == true ? $this->getUrl() : $this->_cdn ) ;
	}

	public static function getInstance()
	{
		if ( self::$instance === NULL ) self::$instance = new Http;
		return self::$instance ;
	}

    private function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }
	
	/* ************************************************** */
	/* ****************   FUNCTIONS   ******************* */
	/* ************************************************** */

    public function vendor( $url )
    {
        return $this->getCdn() . '/assets/vendor/' . ltrim($url, '/');
    }

    public function assetAdmin( $url )
    {
        return $this->getCdn() . '/assets/vendor/' . VENDOR_CMS . "/" . ltrim($url, '/');
    }
	
	private function load()
	{
		$content = \DB::for_table('param')
            ->select('param_key')
            ->select('param_value')
            ->where_equal('param_key','server_cdn')
            ->find_one();
		
		if ( $content )
		{
			if ( $content->param_value === NULL or $content->param_value == '' ) 	$this->_cdn = $this->getUrl();
			else									                                $this->_cdn = 'http://' . $content->param_value ;
		}
	}
}