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
		if ( FILE_CONFIG !== false ) $this->load() ;
	}
	
	/* ************************************************** */
	/* ****************    SETTER     ******************* */
	/* ************************************************** */ 
	
	
	
	/* ************************************************** */
	/* ****************     GETTER    ******************* */
	/* ************************************************** */

	public function getUrl()
	{
//        return $+this->CMS()->getApp()->request()->getUrl() ;
        if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_PROTO'] , 'https' ) !== false ) $_SERVER['HTTPS'] = 'on';
        if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];

        $protocol = 'http' ;
        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) $protocol = 'https' ;
        return $protocol . '://' . $_SERVER['HTTP_HOST'] ;
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