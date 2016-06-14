<?php

namespace App\Kernel\Back;

class Repository extends \App\Kernel\Repository
{
    public function findOne( $id )
    {
        return \DB::for_module( $this->getName() )->where_id_is( $id )->find_one();
    }

    public function findOneLang( $id , $idlang )
    {
        return \DB::for_module_lang( $this->getName() , $id , $idlang )->find_one() ;
    }

    /* Fonction appelée par "add" & "update" */
    public function pushDataAssoc( $nameField , $field , $id )
    {
        // On supprime tous les infos en base
        \DB::for_module_assoc( $this->getName() , $nameField )
            ->where_equal( \DB::getTableNameAssoc( $this->getName() , $nameField ) . '_' . \DB::getIdName( $this->getName() ) , $id )
            ->delete_many();

        // On insere
        if ( $field->getValue() !== NULL && is_array( $field->getValue() ) )
        {
            foreach( $field->getValue() as $row )
            {
                \DB::add_assoc( $this->getName() , $nameField , $this->getId() , $row );
            }
        }
    }

    public function create()
    {
        return \DB::for_module( $this->getName() )->create();
    }

    public function createLang()
    {
        return \DB::for_module_lang( $this->getName() )->create();
    }

    public function checkDatabase()
    {
        \DB::checkModuleTable( $this->getName() , $this->getEntity()->hasMultiLang() , $this->getEntity()->getField() ) ;
    }
}