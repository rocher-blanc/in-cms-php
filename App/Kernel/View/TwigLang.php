<?php

namespace App\Kernel\View;

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
            new \Twig_SimpleFunction('_', array($this, 'trad'))
        );
    }

    public function trad( $key )
    {
        return \App\Kernel\Front\Translate::getInstance()->getText( $key ) ;
    }
}