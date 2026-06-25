<?php

namespace App\Kernel\View;

use App\Kernel\Front\Helper;

class TwigHelper extends \Twig\Extension\AbstractExtension
{
    public function getName()
    {
        return 'helper';
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('HelperModule', array($this, 'helperModule')),
            new \Twig\TwigFunction('HelperPage', array($this, 'helperPage'))
        ];
    }

    /*
     * Accessible dans les controllers des entities
     */
    public function helperModule( $entity , $method , $arg = [] )
    {
        $helper = new Helper;
        $helper->setEntity( $entity ) ;
        return $helper->getModuleHelper( $method , $arg );
    }

    /*
     * Accessible dans les controllers des pages
     */
    public function helperPage( $page , $method , $arg = [] )
    {
        $helper = new Helper;
        $helper->setPage( $page ) ;
        return $helper->getPageHelper( $method , $arg );
    }
}