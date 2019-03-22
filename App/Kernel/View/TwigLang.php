<?php

namespace App\Kernel\View;

use App\Kernel\Front\Translate;
use Slim\Slim;

class TwigLang extends \Twig_Extension
{
    public function getName()
    {
        return 'lang';
    }

    public function getFunctions()
    {
       return array(
            new \Twig_SimpleFunction('_', array($this, 'trad')),
            new \Twig_SimpleFunction('__', array($this, 'exist')),
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