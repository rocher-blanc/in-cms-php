<?php

namespace App\Kernel\View;

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
            new \Twig_SimpleFunction('vendor', array($this, 'vendor')),
            new \Twig_SimpleFunction('route', array($this, 'route')),
            new \Twig_SimpleFunction('asset', array($this, 'asset'))
        );
    }

    public function route( $module , $type = '' , $parent = '' , $id = NULL , $token = NULL )
    {
        return \App\Kernel\Factory::getInstance()->Url()->route( $module , $type , $parent , $id , $token ) ;
    }

    public function vendor( $url )
    {
        return \App\Kernel\Http::getInstance()->vendor( $url ) ;
    }

    public function asset($url)
    {
        if ( ! DEBUG )
        {
            // $url = str_replace('.js' , '.min.js' , $url ) ;
            // $url = str_replace('.css' , '.min.css' , $url ) ;
        }

        return $this->vendor( VENDOR_CMS . '/' . ltrim($url, '/') ) ;
    }
}