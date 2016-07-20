<?php

namespace App\Kernel\View;

use Slim\Slim;

class TwigDebug extends \Twig_Extension
{
    public function getName()
    {
        return 'debug';
    }

    public function getFunctions()
    {
       return [
           new \Twig_SimpleFunction('dump', [ $this, 'twig_var_dump']),
       ];
    }

    public function twig_var_dump()
    {
        if ( ! DEBUG ) return;

        ob_start();
        if ( func_num_args() > 0 )
        {
            foreach( func_get_args() as $row )
            {
                \App\Kernel\Debug::dump( $row , null , false , false ) ;
            }
        }
        return ob_get_clean();
    }
}