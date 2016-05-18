<?php

namespace App\Kernel\Front;

class Seo 
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */

	private $_module_id ;
	private $_element_id ;
	private $_url = '' ;
	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct() {}
	
	/* ************************************************** */
	/* ****************    SETTER     ******************* */
	/* ************************************************** */ 
	
	public function setModuleId( $var )
	{
		$this->_module_id = $var ;
	}

    public function setElementId( $var )
    {
        $this->_element_id = $var ;
    }
	
	/* ************************************************** */
	/* ****************     GETTER    ******************* */
	/* ************************************************** */

    public function getModuleId()
    {
        return $this->_module_id ;
    }

    public function getElementId()
    {
        return $this->_element_id ;
    }
	
	private function Lang()
	{
		return \App\Kernel\Lang::getInstance() ;
	}
	
	/* ************************************************** */
	/* ****************   FUNCTIONS   ******************* */
	/* ************************************************** */

    public function getUrl()
    {
        $row = \DB::for_table('seo')
            ->select('seo_url')
            ->where([ 'seo_element_id' => $this->getElementId() , 'seo_module_id' => $this->getModuleId(), 'seo_lang_id' => $this->Lang()->getActive()->id ])
            ->find_one();

        if ( $row ) return $row->seo_url ;
        else        return '' ;
    }
}