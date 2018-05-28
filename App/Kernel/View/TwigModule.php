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

    private function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

    private function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    public function getFunctions()
    {
       return array(
            new \Twig_SimpleFunction('component', [$this, 'component']),
            new \Twig_SimpleFunction('form', [$this, 'form']),
            new \Twig_SimpleFunction('parse', [$this, 'parse']),
        );
    }

    public function component( $module , $component , $type , $request = [] , $vars = [] )
    {
        if ( is_string( $request ) ) $request = [];

        $replaceString = '' ;
        $fullUrl = $this->Factory()->Url()->getFullUrl() ;
        if ( $this->Lang()->count() > 1 ) $replaceString.= $this->getUrl(0) . '/' ;
        $url = ltrim( str_replace( "/" . $replaceString . "/" , '' , $fullUrl ) , '/') ;

        $Controller = $this->Container()->module( $module )->getController();
        $Controller->setUrl( explode('/',$url) );
        $Controller->setComponentName( $component );

        return $Controller->getComponent( $type , $request , $vars );
    }

    public function form( $module , $type = 'html' )
    {
        return $this->Container()->module( $module )->getController()->getForm( $type );
    }

    public function parse( $field )
    {
        if ( array_key_exists( 'id' , $field ) == true &&
            array_key_exists( 'type' , $field ) == true &&
            array_key_exists( 'value' , $field ) == true &&
            array_key_exists( 'name' , $field ) == true &&
            array_key_exists( 'module' , $field ) == true )
        {
            return $this->Container()->module( $field['module'] )->getController()->subParse( $field );
        }
    }
}