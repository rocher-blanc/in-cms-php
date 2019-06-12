<?php

namespace App\Kernel\View;

use App\Kernel\Container;
use App\Kernel\Factory;
use App\Kernel\Lang;
use Slim\Slim;

class TwigModule extends \Twig_Extension
{
    public function getName()
    {
        return 'module';
    }

    private function Factory()
    {
        return Factory::getInstance() ;
    }

    private function Container()
    {
        return Container::getInstance() ;
    }

    private function Lang()
    {
        return Lang::getInstance() ;
    }

    public function getFunctions()
    {
       return [
            new \Twig_SimpleFunction('component', [$this, 'component']),
            new \Twig_SimpleFunction('module', [$this, 'module']),
            new \Twig_SimpleFunction('parse', [$this, 'parse']),
       ];
    }

    public function module( $module , $type , $request = [] )
    {
        if ( is_string( $request ) ) $request = [];

        $replaceString = '' ;
        $fullUrl = $this->Factory()->Url()->getFullUrl() ;
        if ( $this->Lang()->count() > 1 ) $replaceString.= $this->Lang()->getActive()->url . '/' ;
        $url = ltrim( str_replace( "/" . $replaceString . "/" , '' , $fullUrl ) , '/') ;

        $Controller = $this->Container()->module( $module )->getController();
        $Controller->setUrl( explode('/',$url) );

        return $Controller->getElement( $type , $request );
    }

    public function component( $module , $component , $type , $request = [] , $vars = [] )
    {
        if ( is_string( $request ) ) $request = [];

        $replaceString = '' ;
        $fullUrl = $this->Factory()->Url()->getFullUrl() ;
        if ( $this->Lang()->count() > 1 ) $replaceString.= $this->Lang()->getActive()->url . '/' ;
        $url = ltrim( str_replace( "/" . $replaceString . "/" , '' , $fullUrl ) , '/') ;

        $Controller = $this->Container()->module( $module )->getController();
        $Controller->setUrl( explode('/',$url) );
        $Controller->setComponentName( $component );

        return $Controller->getComponent( $type , $request , $vars );
    }

    public function parse( $field )
    {
        if ( is_array( $field ) )
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
        else
        {
            return [];
        }
    }
}