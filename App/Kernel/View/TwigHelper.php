<?php

namespace App\Kernel\View;

use Slim\Slim;

class TwigHelper extends \Twig_Extension
{
    public function getName()
    {
        return 'helper';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('HelperModule', array($this, 'helperModule')),
            new \Twig_SimpleFunction('HelperPage', array($this, 'helperPage'))
        ];
    }

    /*
     * Accessible dans les controllers des entities
     */
    public function helperModule( $entity , $method , $arg = [] )
    {
        $helper = new \App\Kernel\Front\Helper;
        $helper->setEntity( $entity ) ;
        return $helper->getModuleHelper( $method , $arg );
    }

    /*
     * Accessible dans les controllers des pages
     */
    public function helperPage( $page , $method , $arg )
    {
        $helper = new \App\Kernel\Front\Helper;
        $helper->setPage( $page ) ;
        return $helper->getPageHelper( $method , $arg );
    }
}