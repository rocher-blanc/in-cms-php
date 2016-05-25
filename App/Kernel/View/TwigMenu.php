<?php

namespace App\Kernel\View;

use Slim\Slim;

class TwigMenu extends \Twig_Extension
{
    public function getName()
    {
        return 'menu';
    }

    private function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    private function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('Menu', array($this, 'menu'))
        ];
    }

    public function menu( $id , $type = 'object' )
    {
        $menu   = new \App\Kernel\Front\Menu;
        $result = $menu->load( $id ) ;

        if ( $type == 'object' )
        {
            return $result ;
        }
        else
        {
            return $this->CMS()->fetch( 'helper/menu/view.twig.html' , [
                'id' => $id ,
                'menu' => $result ,
                'responsive' => ( $type == "responsive" ? true : false )
            ]) ;
        }
    }
}