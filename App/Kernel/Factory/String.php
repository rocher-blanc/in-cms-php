<?php

namespace App\Kernel\Factory;

class String
{
    public function camelCase( $str ) {
        $i = array("-","_");

        $str = preg_replace('/([a-z])([A-Z])/', "\\1 \\2", $str);
        $str = preg_replace('@[^a-zA-Z0-9\-_ ]+@', '', $str);
        $str = str_replace($i, ' ', $str);
        $str = str_replace(' ', '', ucwords(strtolower($str)));
        $str = strtolower(substr($str,0,1)).substr($str,1);

        return $str;
    }

    public function uncamelCase( $str ) {
        $str = preg_replace('/([a-z])([A-Z])/', "\\1_\\2", $str);
        $str = strtolower($str);

        return $str;
    }
}