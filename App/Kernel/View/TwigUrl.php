<?php

namespace App\Kernel\View;

use Slim\Slim;

class TwigUrl extends \Twig_Extension
{
    public function getName()
    {
        return 'url';
    }

    private function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    public function getFunctions()
    {
       return array(
            new \Twig_SimpleFunction('urlpage', array($this, 'urlpage')),
            new \Twig_SimpleFunction('urlmodule', array($this, 'urlmodule')),
        );
    }

    public function urlpage( $id )
    {
        $cLang = \DB::for_table('page_lang')
			->select('page_lang_url')
			->where(['page_lang_page_id' => $id, 'page_lang_lang_id' => \App\Kernel\Lang::getInstance()->getActive()->id])
			->find_one();
		
		if ( $cLang ) 	return \App\Kernel\Http::getInstance()->getUrl() . '/' . ( \App\Kernel\Lang::getInstance()->count() > 1 ? \App\Kernel\Lang::getInstance()->getActive()->url . "/" : '' ) . $cLang->page_lang_url ;
		else			return "#" ;
    }


    public function urlmodule( $id )
    {
        $cLang = \DB::for_table('module_lang')
			->select('module_lang_url')
			->where(['module_lang_module_id' => $id, 'module_lang_lang_id' => \App\Kernel\Lang::getInstance()->getActive()->id])
			->find_one();
		
		if ( $cLang ) 	return \App\Kernel\Http::getInstance()->getUrl() . '/' . ( \App\Kernel\Lang::getInstance()->count() > 1 ? \App\Kernel\Lang::getInstance()->getActive()->url . "/" : '' ) . $cLang->module_lang_url ;
		else			return "#" ;
    }
}