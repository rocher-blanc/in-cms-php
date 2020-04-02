<?php

namespace App\Kernel\View;

use App\Kernel\Factory;
use App\Kernel\Http;
use App\Kernel\CMS;
use Slim\Slim;

class TwigAdmin extends \Twig_Extension
{
    public function getName()
    {
        return 'admin';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('siteUrl', [$this, 'site']),
            new \Twig_SimpleFunction('vendor', array($this, 'vendor')),
            new \Twig_SimpleFunction('route', array($this, 'route')),
            new \Twig_SimpleFunction('dRoute', array($this, 'depedencyRoute')),
            new \Twig_SimpleFunction('asset', array($this, 'asset'))
        );
    }

    public function site($url, $withUri = true, $appName = 'default')
    {
        return Http::getInstance()->getUrl() . '/' . CMS::getInstance()->config('admin.url') . '/' . ltrim($url, '/');
    }

	public function route( $module , $type = '' , $parent = '' , $id = NULL , $token = NULL )
	{
		return Factory::getInstance()->Url()->route( $module , $type , $parent , $id , $token ) ;
	}

	public function depedencyRoute( $module , $action , $id , $id_module , $id_element = NULL )
	{
		return Factory::getInstance()->Url()->depedencyRoute( $module , $action , $id , $id_module , $id_element ) ;
	}

    public function vendor( $url )
    {
        return Http::getInstance()->vendor( $url ) ;
    }

    public function asset($url)
    {
        if ( ! DEBUG_CMS )
        {
            // $url = str_replace('.js' , '.min.js' , $url ) ;
            // $url = str_replace('.css' , '.min.css' , $url ) ;
        }

        return $this->vendor( VENDOR_CMS . '/' . ltrim($url, '/') ) ;
    }
}