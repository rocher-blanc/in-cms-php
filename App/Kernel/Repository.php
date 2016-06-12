<?php

class Repository
{
    public function __construct( $name )
    {
        $this->name = $name ;
    }

    public function getName()
    {
        return $this->name ;
    }

    public function getEntity()
    {
        return $this->Container()->module( $this->getName() )->getEntity() ;
    }
}