<?php

namespace App\Kernel\Back;

class Repository extends \App\Kernel\Common\Repository
{
    public function checkIfPatchTable( $id )
    {
        if ( \App\Kernel\Container::getInstance()->param()->get('key_module_' . $id ) != md5_file( ENTITY_PATH . "/" . $this->getName() . ".php" ) )
        {
            \DB::patchModuleTable( $this->getName() ) ;
            \App\Kernel\Container::getInstance()->param()->set('key_module_' . $id , md5_file( ENTITY_PATH . "/" . $this->getName() . ".php" ) );
        }
    }

    public function findAllForSelect( $target , $alias , $parentName )
    {
        return \DB::find_all_for_select( $this->getName() , $target , $alias , \App\Kernel\Lang::getInstance()->getDefault()->id , $parentName ) ;
    }

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
                \DB::add_assoc( $this->getName() , $nameField , $id , $row );
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

    public function getAllTableIndex()
    {
        $content = \DB::for_module( $this->getName() );

        if ( $this->getEntity()->hasMultiLang() )
        {
            $table 			= \DB::getTableName( $this->getName() ) ;
            $tableLang  	= \DB::getTableNameLang( $this->getName() ) ;
            $idName			= \DB::getIdName( $this->getName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getName() ) ;

            $content = $content->left_outer_join( $tableLang , array( $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ))
                ->where_equal($tableLang . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getDefault()->id );
        }

        if ( $this->getEntity()->hasOrder() )
        {
            $content = $content->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
        }
        else
        {
            $content = $content->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ;
        }

        return $content->find_many();
    }

    public function getOnIndex( $id_module )
    {
        $tbl    = \DB::getTableName( $this->getName() );
        $idName	= \DB::getIdName( $this->getName() ) ;

        if ( ! $this->getEntity()->get( $this->getEntity()->getUrlName() )->hasLang() )   $tblField = \DB::getTableName( $this->getName() );
        else                                                                              $tblField = \DB::getTableNameLang( $this->getName() );

        $seo_module_one = \DB::for_module( $this->getName() )
            ->select( 'seo.seo_title' )
            ->select( 'seo.seo_description' )
            ->select( 'seo.seo_keyword' )
            ->select( $tbl . '.' . $idName , 'id' )
            ->select( $tblField . '.' . $this->getEntity()->get( $this->getEntity()->getUrlName() )->getColumn() , 'value' )
            ->left_outer_join( 'seo' , [ 'seo.seo_element_id' , '=', $tbl . '.' . $idName ] );

        if ( $this->getEntity()->get( $this->getEntity()->getUrlName() )->hasLang() )
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