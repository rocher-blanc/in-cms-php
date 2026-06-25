<?php

namespace App\Kernel\View;

use App\Kernel\Factory;
use App\Kernel\Http;
use App\Kernel\CMS;

class TwigAdmin extends \Twig\Extension\AbstractExtension
{
    public function getName()
    {
        return 'admin';
    }

    public function getFunctions()
    {
        return array(
            new \Twig\TwigFunction('siteUrl', [$this, 'site']),
            new \Twig\TwigFunction('vendor', array($this, 'vendor')),
            new \Twig\TwigFunction('route', array($this, 'route')),
            new \Twig\TwigFunction('dRoute', array($this, 'depedencyRoute')),
            new \Twig\TwigFunction('asset', array($this, 'asset'))
        );
    }

    public function site($url, $withUri = true, $appName = 'default')
    {
        if ( strpos( CMS::getInstance()->config('admin.url') , $url ) !== false )
        {
            $withUri = false ;
        }

        return Http::getInstance()->getUrl() . ( ! $withUri ? '' : CMS::getInstance()->config('admin.url') . '/' ) . ltrim($url, '/');
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
        return $this->vendor( VENDOR_CMS . '/' . ltrim($url, '/') ) ;
    }
}