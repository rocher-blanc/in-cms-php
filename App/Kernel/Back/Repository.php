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
        \DB::checkModuleTable( $this->getName() , \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity()->hasMultiLang() , \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity()->getField() ) ;
    }

    public function getOnIndex( $urlField , $id_module )
    {
        $this->checkDatabase() ;

        $tbl    = \DB::getTableName( $this->getName() );
        $idName	= \DB::getIdName( $this->getName() ) ;

        if ( ! $urlField->hasLang() )   $tblField = \DB::getTableName( $this->getName() );
        else                            $tblField = \DB::getTableNameLang( $this->getName() );

        $seo_module_one = \DB::for_module( $this->getName() )
            ->select( 'seo.seo_title' )
            ->select( 'seo.seo_description' )
            ->select( 'seo.seo_keyword' )
            ->select( $tbl . '.' . $idName , 'id' )
            ->select( $tblField . '.' . $urlField->getColumn() , 'value' )
            ->left_outer_join( 'seo' , [ 'seo.seo_element_id' , '=', $tbl . '.' . $idName ] );

        if ( $urlField->hasLang() )
        {
            $seo_module_one->left_outer_join( $tblField , array( $tbl . '.' . $idName , '=', $tblField . '.' . \DB::getIdNameInLang( $this->getName() ) ))
                ->where_equal($tblField . '.' . \DB::getLangIdLangName( $this->getName() ) , \App\Kernel\Lang::getInstance()->getDefault()->id);
        }

        $seo_module_one->where_equal('seo.seo_module_id', $id_module)
            ->where_in('seo.seo_lang_id',\App\Kernel\Lang::getInstance()->getTabLang() )
            ->where_raw("(seo.seo_title IS NULL OR seo.seo_description IS NULL OR seo.seo_keyword IS NULL)",[])
            ->group_by('seo.seo_element_id')
            ->find_many();
    }
}