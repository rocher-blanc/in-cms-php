<?php

namespace App\Kernel\Front;

abstract class Page
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    public $tpl  = '' ;
    public $id   = NULL ;
	public $_var = [] ;
	
	/* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
	{
		
	}

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setId( $var )
    {
        $this->id = $var ;
        $this->setTemplate( 'page-' . $var . '.twig.html' );
    }

    protected function setTemplate( $var )
    {
        $this->tpl = $var ;
    }

    protected function setVar( $key , $value )
    {
        $this->_var[ $key ] = $value ;
    }
	
    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function CMS()
    {
        return  \App\Kernel\CMS::getInstance() ;
    }

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function getTemplate()
    {
        return $this->tpl ;
    }

    protected function getId()
    {
        return $this->id ;
    }

    protected function getVar()
    {
        return $this->_var ;
    }
 
    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    protected function render()
    {
        $this->CMS()->render( 'page/' . $this->getTemplate() , $this->getVar() ) ;
    }

    public function execute()
    {
        $this->loadMeta() ;
        $this->render() ;
    }

    public function post( $key )
    {
        return $this->CMS()->request()->post( $key );
    }

    /* ************************************************** */
    /* *****************     META     ******************* */
    /* ************************************************** */

    protected function loadMeta()
    {
        $result = \DB::for_table('page')
            ->select('page_lang.page_lang_url')
            ->select('page_lang.page_lang_title')
            ->select('page_lang.page_lang_description')
            ->select('page_lang.page_lang_keyword')
            ->select('page.page_index')
            ->left_outer_join('page_lang', [ 'page_lang.page_lang_page_id', '=', 'page.page_id' ])
            ->where(['page_lang.page_lang_lang_id' => $this->Lang()->getActive()->id, 'page_lang.page_lang_page_id' => $this->getId() ])
            ->find_one();

        if ( $result )
        {
            $lastTab = $this->CMS()->view()->getData('meta') ;
            $robots = $lastTab['robots'] ;

            if ( $result->module_index == 0 && substr( $robots , 0 , 5 ) == 'index' )
            {
                $robots = "no" . $robots ;
            }

            $meta = [
                'url' => \App\Kernel\Http::getInstance()->getUrl() . '/' . $result->page_lang_url,
                'title' => $result->page_lang_title,
                'description' => $result->page_lang_description,
                'keyword' => $result->page_lang_keyword,
                'robots' => $robots
            ];
			
			$og = [
                'type' => "article",
                'title' => $result->page_lang_title,
                'description' => $result->page_lang_description
            ];
			
            $meta = array_merge( $this->CMS()->view()->getData('meta') , $meta ) ;
            $this->setVar('meta',$meta);
			
            $og = array_merge( $this->CMS()->view()->getData('og') , $og ) ;
            $this->setVar('og',$og);
        }
    }
}