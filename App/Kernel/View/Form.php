<?php

namespace App\Kernel\View;

use App\Kernel\Container;
use App\Kernel\Front\Former;

class Form extends \Twig_Extension
{
    public function getName()
    {
        return 'form';
    }

    private function Container()
    {
        return Container::getInstance() ;
    }

    public function getFunctions()
    {
       return [
            new \Twig_SimpleFunction('form', [$this, 'form']),
            new \Twig_SimpleFunction('formDelete', [$this, 'formDelete']),
            new \Twig_SimpleFunction('form_init', [$this, 'formInit']),
       ];
    }

    public function form( $module , $id = NULL , $url = '', $timer = '' , $data = [] )
    {
        return $this->Container()->module( $module )->getController()->getForm( $id , $url , $timer , $data );
    }

    public function formDelete( $module , $id , $var = [], $url = '' )
    {
        return $this->Container()->module( $module )->getController()->getFormDelete( $id , $var , $url );
    }

    public function formInit( $module , $id = NULL , $url = '', $timer = '' , $data = [] )
    {
        $init = $this->Container()->module( $module )->getController()->getCustomForm( $id , $url , $timer , $data );

        return new Former( $init );
    }
}