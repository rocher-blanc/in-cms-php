<?php

namespace App\Kernel\Common;

class Model
{
    protected $obj = NULL ;

    public function __construct()
    {
        $this->obj = new \stdClass;
    }

    public function set( $key , $value )
    {
        $this->obj->$key = $value ;
    }

    public function get( $key )
    {
        return $this->obj->$key ;
    }
}