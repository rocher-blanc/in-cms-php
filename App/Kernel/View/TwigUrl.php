<?php

namespace App\Kernel\View;

use App\Kernel\CMS;
use App\Kernel\Factory;
use App\Kernel\Http;
use Slim\Slim;

class TwigUrl extends \Twig_Extension
{
    public function getName()
    {
        return 'url';
    }

    private function Factory()
    {
        return Factory::getInstance() ;
    }

    public function getFunctions()
    {
       return array(
            new \Twig_SimpleFunction('urlpage', [$this, 'urlpage']),
            new \Twig_SimpleFunction('urlmodule', [$this, 'urlmodule']),
            new \Twig_SimpleFunction('siteUrl', [$this, 'site']),
        );
    }

    public function site($url, $withUri = true, $appName = 'default')
    {
        $uri = '' ;

        if ( CMS::getInstance()->config('admin.url') !== NULL )
        {
            $uri = CMS::getInstance()->config('admin.url') . '/' ;
        }

        return Http::getInstance()->getUrl() . '/' . $uri . ltrim($url, '/');
    }

    public function urlpage( $id )
    {
        return $this->Factory()->Url()->page( $id , true ) ;
    }

    public function urlmodule( $id , $domain = NULL )
    {
        return $this->Factory()->Url()->module( $id , $domain ) ;
    }
}