<?php

namespace App\Kernel\Front;

class Redirect
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private $_url = '' ;

	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */
	
	public function __construct() {}
	
	/* ************************************************** */
	/* ****************    SETTER     ******************* */
	/* ************************************************** */ 

    public function setUrl( $var )
    {
        $this->_url = $var ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function getUrl()
    {
        return $this->_url ;
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
	
	public function check301()
	{
        if ( $this->urlExist() )
        {
            $this->redirect();
        }
	}

    private function urlExist()
    {
        $ct = \DB::for_table('redirect_301')
            ->where(['redirect_301_oldurl' => $this->getUrl()])
            ->count();

        if ( $ct == 1 ) return true ;
        else            return false ;
    }

    private function redirect()
    {
        $redirect = \DB::for_table('redirect_301')
            ->where(['redirect_301_oldurl' => $this->getUrl()])
            ->find_one();

        if ( $redirect )
        {
            $this->Factory()->Response()->redirect( \App\Kernel\Http::getInstance()->getUrl() . $redirect->redirect_301_newurl , 301 );
        }

        $url = '' ;

        if ( $this->Lang()->count() > 1 )
        {
            $url.= $this->Lang()->get( $redirect->redirect_lang_id )->url . "/" ;
        }

        if ( $redirect->redirect_page_id !== NULL )
        {
            // page speciale
            $page = \DB::for_table('page')
                ->select('page_lang.page_lang_url')
                ->left_outer_join('page_lang', array('page.page_id', '=', 'page_lang.page_lang_page_id'))
                ->where(['page_lang.page_lang_lang_id' => $redirect->redirect_lang_id, 'page.page_id' => $redirect->redirect_page_id])
                ->where_equal('page.page_active', 1)
                ->where_equal('page.page_default', 0)
                ->find_one();

            if ( $page )
            {
                $url.= $page->page_lang_url ;
            }
        }
        else
        {
            // module
            $module = \DB::for_table('module')
                ->left_outer_join('module_lang', array('module.module_id', '=', 'module_lang.module_lang_module_id'))
                ->where(['module_lang.module_lang_lang_id' => $redirect->redirect_lang_id, 'module.module_id' => $redirect->redirect_module_id])
                ->where_equal('module.module_active', 1)
                ->find_one();

            if ( $module )
            {
                if ( $redirect->redirect_element_id !== NULL )
                {
                    if ( $module->module_defaut == 0 )
                    {
                        $url.= $module->module_lang_url . '/' ;
                    }

                    $result = \DB::for_table('seo')
                        ->select('seo_url')
                        ->where(['seo_lang_id' => $redirect->redirect_lang_id, 'seo_module_id' => $redirect->redirect_module_id, 'seo_element_id' => $redirect->redirect_element_id])
                        ->find_one();

                    if ( $result )
                    {
                        $url.= $result->seo_url ;
                    }
                    else
                    {
                        $url.= '' ;
                    }
                }
                else
                {
                    $url.= $module->module_lang_url ;
                }
            }
            else
            {
                $url = '' ;
            }
        }

        $this->Factory()->Response()->redirect( \App\Kernel\Http::getInstance()->getUrl() . '/' . $url , 301 );
    }
}