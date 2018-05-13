<?php

namespace App\Kernel\View;

use Slim\Slim;

class TwigModule extends \Twig_Extension
{
    public function getName()
    {
        return 'module';
    }

    private function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    public function getFunctions()
    {
       return array(
            new \Twig_SimpleFunction('component', [$this, 'component']),
            new \Twig_SimpleFunction('form', [$this, 'form']),
        );
    }

    public function component( $module , $component , $type , $request = [] , $vars = [] )
    {
        if ( is_string( $request ) ) $request = [];

        $Controller = \App\Kernel\Container::getInstance()->module( $module )->getController();
        $Controller->setComponentName( $component );

        return $Controller->getComponent( $type , $request , $vars );
    }

    public function form( $module , $type = 'html' )
    {
        $Controller = \App\Kernel\Container::getInstance()->module( $module )->getController();

        return $Controller->getForm( $type );
    }
}