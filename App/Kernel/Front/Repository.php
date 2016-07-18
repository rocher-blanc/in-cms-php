<?php

namespace App\Kernel\Front;

class Repository extends \App\Kernel\Repository
{
    protected $_limit_get_all = NULL;

    public function getLimitGetAll()
    {
        return $this->_limit_get_all ;
    }

    public function findOne( $id )
    {
        $table = \DB::getTableName( $this->getName() ) ;

        /* Requete pour aller chercher les données */
        $result = \DB::for_module( $this->getName() )->where_id_is( $id );
        if ( $this->getEntity()->hasValidation() ) $result = $result->where_equal( $table . "." . $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , 1 );
        if ( $this->getEntity()->hasMultiLang() )
        {
            $tableLang  	= \DB::getTableNameLang( $this->getName() ) ;
            $idName			= \DB::getIdName( $this->getName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getName() ) ;

            $result = $result->left_outer_join( $tableLang , [ $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ] )
                ->where_equal( $tableLang . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getActive()->id );
        }

        return $result->find_one();
    }

    public function findIn( $tab )
    {
        $idName	= \DB::getIdName( $this->getName() ) ;

        return $this->getKit()->where_in( $idName , $tab )->find_many();
    }

    public function findAll()
    {
        return $this->getKit()->find_many();
    }

    public function getKit()
    {
        $table = \DB::getTableName( $this->getName() ) ;

        $all = \DB::for_module( $this->getName() );
        if ( $this->getLimitGetAll() !== NULL ) $all = $all->limit( $this->getLimitGetAll() );

        if ( $this->getEntity()->hasValidation() ) 	$all = $all->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->fieldSql() , 1 );

        if ( $this->getEntity()->hasMultiLang() )
        {
            $tableLang  	= \DB::getTableNameLang( $this->getName() ) ;
            $idName			= \DB::getIdName( $this->getName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getName() ) ;

            $all = $all->left_outer_join( $tableLang , [ $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ] )
                ->where_equal( $tableLang . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getActive()->id );
        }

        if ( $this->getEntity()->hasOrder() ) 	$all = $all->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() );
        else									$all = $all->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );

        return $all;
    }
    
    public function lastUpdated()
    {
        $result = \DB::for_module( $this->getName() )
            ->select( $this->getEntity()->get('date_updated')->getColumn() )
            ->limit(1);

        if ( $this->getEntity()->hasValidation() ) $result = $result->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , 1 );

        if ( $this->getEntity()->hasOrder() ) 	$result = $result->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() );
        else									$result = $result->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() );

        return $result->find_one();
    }
    
    public function getAssocValue( $nameField , $id )
    {
        return \DB::for_module_assoc( $this->getName() , $nameField )
            ->select( \DB::getTableNameAssocValue( $this->getName() , $nameField ) , 'value' )
            ->where_equal( \DB::getTableNameAssoc( $this->getName() , $nameField ) . '_' . \DB::getIdName( $this->getName() ) , $id )
            ->find_many();
    }

    public function count()
    {
        return \DB::for_module( $this->getName() )->count();
    }
}