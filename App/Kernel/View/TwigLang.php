<?php

namespace App\Kernel\View;

use App\Kernel\Front\Translate;

class TwigLang extends \Twig\Extension\AbstractExtension
{
    public function getName()
    {
        return 'lang';
    }

    public function getFunctions()
    {
       return array(
            new \Twig\TwigFunction('_', array($this, 'trad')),
            new \Twig\TwigFunction('__', array($this, 'exist')),
        );
    }

    public function trad( $key , $var = [] )
    {
        return Translate::getInstance()->getText( $key , $var ) ;
    }

    public function exist( $key )
    {
        return Translate::getInstance()->exist( $key ) ;
    }
}