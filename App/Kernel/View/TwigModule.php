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
        );
    }

    public function component( $module , $component , $type , $request = [] , $vars = [] )
    {
        if ( is_string( $request ) ) $request = [];

        $Controller = \App\Kernel\Container::getInstance()->module( $module )->getController();
        $Controller->setComponentName( $component );

        return $Controller->getComponent( $type , $request , $vars );
    }
}