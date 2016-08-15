<?php

namespace App\Kernel\View;

use Slim\Slim;

class TwigUrl extends \Twig_Extension
{
    public function getName()
    {
        return 'url';
    }

    private function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    public function getFunctions()
    {
       return array(
            new \Twig_SimpleFunction('urlpage', array($this, 'urlpage')),
            new \Twig_SimpleFunction('urlmodule', array($this, 'urlmodule')),
        );
    }

    public function urlpage( $id )
    {
        return \App\Kernel\Http::getInstance()->getUrl() . '/' . $this->Factory()->Url()->page( $id ) ;
    }

    public function urlmodule( $id )
    {
        return \App\Kernel\Http::getInstance()->getUrl() . '/' . $this->Factory()->Url()->module( $id ) ;
    }
}