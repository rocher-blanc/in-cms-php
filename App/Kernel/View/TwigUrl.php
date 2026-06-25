<?php

namespace App\Kernel\View;

use App\Kernel\Factory;

class TwigUrl extends \Twig\Extension\AbstractExtension
{
    public function getName()
    {
        return 'url';
    }

    private function Factory()
    {
        return Factory::getInstance() ;
    }

    public function getFunctions()
    {
       return array(
            new \Twig\TwigFunction('urlpage', [$this, 'urlpage']),
            new \Twig\TwigFunction('urlmodule', [$this, 'urlmodule']),
        );
    }

    public function urlpage( $id )
    {
        return $this->Factory()->Url()->page( $id , true ) ;
    }

    public function urlmodule( $id , $domain = NULL )
    {
        return $this->Factory()->Url()->module( $id , $domain ) ;
    }
}