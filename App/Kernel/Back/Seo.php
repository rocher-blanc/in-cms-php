<?php

namespace App\Kernel\Back;

class Seo 
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private $_seo_id ;
	private $_module_id ;
	private $_element_id ;
	private $_lang_id ;
	private $_last_url = '' ;
	private $_url = '' ;
	private $_title = '' ;
	private $_index = 1 ;
	private $_description = '' ;

	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct() {}
	
	/* ************************************************** */
	/* ****************    SETTER     ******************* */
	/* ************************************************** */ 
	
	public function setId( $var )
	{
		$this->_seo_id = $var ;
	}
	
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

	public function setUrl( $var )
	{
		$this->_url = $this->Factory()->Url()->encode( $var );
	}

	public function setLastUrl( $var )
	{
		$this->_last_url = $var ;
	}

    public function setTitle( $var )
    {
        $this->_title = $var ;
        $this->setUrl( $var ) ;
    }

    public function setIndex( $var )
    {
        $this->_index = $var ;
    }

	public function setDescription( $var )
	{
		$this->_description = $var ;
	}
	
	/* ************************************************** */
	/* ****************     GETTER    ******************* */
	/* ************************************************** */
	
	public function getId()
	{
		return $this->_seo_id ;
	}
	
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

	public function getUrl()
	{
		return $this->_url ;
	}

	public function getLastUrl()
	{
		return $this->_last_url ;
	}

    public function getIndex()
    {
        return $this->_index ;
    }

    public function getTitle()
    {
        return ( empty( $this->_title ) ? NULL : $this->_title ) ;
    }
	
	public function getDescription()
	{
		return ( empty( $this->_description ) ? NULL : $this->_description ) ;
	}
	
	private function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}
	
	private function Lang()
	{
		return \App\Kernel\Lang::getInstance() ;
	}
	
	protected function getApp()
	{
		return \App\Kernel\Config::getInstance() ; // migrated from SlimBridge
	}
	
	/* ************************************************** */
	/* ****************   FUNCTIONS   ******************* */
	/* ************************************************** */
	
	public function save( $update = false )
	{
		if ( ! $this->exist() ) $row = $this->create() ;
		else					$row = $this->getContent() ;

        $this->uniq() ;

        if ( $update == true && $this->getUrl() != '' && $this->getUrl() != $row->get( 'seo_url' ) )
        {
            $Redirect = new \App\Kernel\Back\Redirect;
            $Redirect->setLastUrl( $row->get( 'seo_url' ) );
            $Redirect->setNewUrl( $this->getUrl() );
            $Redirect->setModuleId( $this->getModuleId() );
            $Redirect->setElementId( $this->getElementId() );
            $Redirect->setLangId( $this->getLangId() );
            $Redirect->check();
        }

        if ( ( $update == true && $this->getUrl() != '' ) or $update == false ) $row->set( 'seo_url' , $this->getUrl() ) ;
		if ( ( $update == true && $this->getTitle() != '' ) or $update == false ) $row->set( 'seo_title' , $this->getTitle() ) ;
		$row->set( 'seo_description' , $this->getDescription() ) ;
		$row->set( 'seo_index' , $this->getIndex() ) ;
		$row->save();
	}

    private function exist()
    {
        $ct = \DB::for_table('seo')
            ->where([ 'seo_element_id' => $this->getElementId() , 'seo_module_id' => $this->getModuleId(), 'seo_lang_id' => $this->getLangId() ])
            ->count();

        if ( $ct == 0 ) return false ;
        else			return true ;
    }

    private function uniq()
    {
		if ( $this->getUrl() == $this->getLastUrl() ) return true ;

        $this->_url = $this->Factory()->Url()->uniq( $this->getUrl() , $this->getLangId() ) ;
        return true ;
    }
	
	private function loadId()
	{
		$row = \DB::for_table('seo')
			->select('seo_id')
			->where([ 'seo_element_id' => $this->getElementId() ,'seo_lang_id' => $this->getLangId() , 'seo_module_id' => $this->getModuleId() ])
			->find_one();
		
		$this->setId( $row->get('seo_id') ) ;
	}
	
	private function create()
	{
		$row = \DB::for_table('seo')->create();
		$row->set( 'seo_element_id' , $this->getElementId() ) ;
        $row->set( 'seo_module_id' , $this->getModuleId() ) ;
        $row->set( 'seo_lang_id' , $this->getLangId() ) ;
		$row->save() ;
		
		$this->setId( $row->get('seo_id') ) ;

        return $row ;
	}
	
	private function getContent()
	{
		return \DB::for_table('seo')
			->where([ 'seo_module_id' => $this->getModuleId() , 'seo_lang_id' => $this->getLangId() , 'seo_element_id' => $this->getElementId() ])
			->find_one();
	}
	
	public function delete()
	{
		\DB::for_table('seo')
			->where([ 'seo_element_id' => $this->getElementId() , 'seo_module_id' => $this->getModuleId() ])
			->delete_many();
	}
	
	public function getAll()
	{
		$array = [] ;
		
		foreach( $this->Lang()->getAll() as $lang )
		{
            $content = \DB::for_table('seo')
                ->where([ 'seo_module_id' => $this->getModuleId() , 'seo_lang_id' => $lang->id , 'seo_element_id' => $this->getElementId() ])
                ->find_one();
			
			$array[ $lang->url ]['title'] 		= $content->seo_title ;
			$array[ $lang->url ]['url'] 		= $content->seo_url ;
			$array[ $lang->url ]['description'] = $content->seo_description ;

            $this->setIndex( $content->seo_index ) ;
		}
		
		return $array ;
	}
	
	public function update()
	{
		foreach( $this->Lang()->getAll() as $lang )
		{
			$this->setTitle( (\App\Kernel\AppContext::request()?->getParsedBody()['seo_title_' . $lang->url] ?? '') );
			$this->setUrl( (\App\Kernel\AppContext::request()?->getParsedBody()['seo_url_' . $lang->url] ?? '') );
			$this->setLastUrl( (\App\Kernel\AppContext::request()?->getParsedBody()['seo_last_url_' . $lang->url] ?? '') );
			$this->setDescription( (\App\Kernel\AppContext::request()?->getParsedBody()['seo_description_' . $lang->url] ?? '') );
			$this->setIndex( ( (\App\Kernel\AppContext::request()?->getParsedBody()['index'] ?? '') == NULL ? 0 : 1 ) );
			$this->setLangId( $lang->id );
			$this->save( true );
		}
	}
}