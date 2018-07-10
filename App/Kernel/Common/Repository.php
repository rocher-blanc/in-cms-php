<?php

namespace App\Kernel\Common;

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

    public function getTblLang()
    {
        return \DB::getTableNameLang( $this->getName() ) ;
    }

    public function getTbl()
    {
        return \DB::getTableName( $this->getName() ) ;
    }

    public function getEntity()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity() ;
    }

    public function create()
    {
        return \DB::for_module( $this->getName() )->create();
    }

	public function count()
	{
		return \DB::for_module( $this->getName() )->count();
	}

    public function findAllForSelect( $target , $alias , $parentName )
    {
        return \DB::find_all_for_select( $this->getName() , $target , $alias , \App\Kernel\Lang::getInstance()->getDefault()->id , $parentName ) ;
    }
}