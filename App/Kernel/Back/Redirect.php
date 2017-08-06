<?php

namespace App\Kernel\Back;

class Redirect
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private $_module_id ;
	private $_element_id ;
	private $_lang_id ;
	private $_last_url = '' ;
	private $_new_url = '' ;
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
	
	public function setLangId( $var )
	{
		$this->_lang_id = $var ;
	}

    public function setNewUrl( $var )
    {
        $this->_new_url = $var ;
    }

    public function setUrl( $var )
    {
        $this->_url = $var ;
    }

	public function setLastUrl( $var )
	{
		$this->_last_url = $var ;
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
	
	public function getLangId()
	{
		return $this->_lang_id ;
	}

	public function getNewUrl()
	{
		return $this->_new_url ;
	}

    public function getUrl()
    {
        return $this->_url ;
    }

    public function getLastUrl()
    {
        return $this->_last_url ;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */
	
	private function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}
	
	private function Lang()
	{
		return \App\Kernel\Lang::getInstance() ;
	}
	
	/* ************************************************** */
	/* ****************   FUNCTIONS   ******************* */
	/* ************************************************** */
	
	public function check()
	{
        if ( $this->newUrlExist() )
        {
            $this->deleteUrl();
        }

        $this->insertUrl() ;
	}

    private function newUrlExist()
    {
        $ct = \DB::for_table('redirect')
            ->where(['redirect_url' => $this->getNewFullUrl()])
            ->count();

        if ( $ct == 1 ) return true ;
        else            return false ;
    }

    private function deleteUrl()
    {
        \DB::for_table('redirect')
            ->where(['redirect_url' => $this->getNewFullUrl()])
            ->delete();

        return true ;
    }

    private function insertUrl()
    {
        $redirect = \DB::for_table('redirect')->create();
        $redirect->redirect_url         = $this->getLastFullUrl();
        $redirect->redirect_module_id   = $this->getModuleId();
        $redirect->redirect_element_id  = $this->getElementId();
        $redirect->redirect_lang_id     = $this->getLangId();
        $redirect->redirect_url         = $this->getLastFullUrl();
        $redirect->save();

        return true ;
    }

    private function getNewFullUrl()
    {
        return $this->getFirstPartUrl() . $this->getNewUrl() ;
    }

    private function getLastFullUrl()
    {
        return $this->getFirstPartUrl() . $this->getLastUrl() ;
    }

    private function getFirstPartUrl()
    {
        $url = '' ;

        if ( $this->Lang()->count() > 1 )
        {
            $url.= $this->Lang()->get( $this->getLangId() )->url . "/" ;
        }

        $module = \DB::for_table('module')
            ->left_outer_join('module_lang', array('module.module_id', '=', 'module_lang.module_lang_module_id'))
            ->where(['module_lang.module_lang_lang_id' => $this->Lang()->get( $this->getLangId() )->id, 'module.module_id' => $this->getModuleId()])
            ->find_one();

        if ( $module->module_defaut == 0 )
        {
            $url.= $module->module_lang_url . '/' ;
        }

        return $url ;
    }
}