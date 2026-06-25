<?php

namespace App\Kernel\View;


class TwigDebug extends \Twig\Extension\AbstractExtension
{
    public function getName()
    {
        return 'debug';
    }

    public function getFunctions()
    {
       return [
           new \Twig\TwigFunction('dump', [ $this, 'twig_var_dump']),
       ];
    }

    public function twig_var_dump()
    {
        if ( ! DEBUG_CMS ) return;

        ob_start();
        if ( func_num_args() > 0 )
        {
            foreach( func_get_args() as $row )
            {
                dump( $row ) ;
            }
        }
        return ob_get_clean();
    }
}