<?php

namespace App\Kernel\Front;

class Repository extends \App\Kernel\Common\Repository
{
    protected $_limit_get_all = NULL;

    public function getLimitGetAll()
    {
        return $this->_limit_get_all ;
    }

    public function getTbl()
    {
        return \DB::getTableName( $this->getName() ) ;
    }

    public function getTblLang()
    {
        return \DB::getTableNameLang( $this->getName() ) ;
    }

    public function findOne( $id )
    {
        /* Requete pour aller chercher les données */
        $result = \DB::for_module( $this->getName() )->where_id_is( $id );
        if ( $this->getEntity()->hasValidation() ) $result = $result->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->fieldSql() , 1 );
        if ( $this->getEntity()->hasMultiLang() )
        {
            $idName			= \DB::getIdName( $this->getName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getName() ) ;

            $result = $result->left_outer_join( $this->getTblLang() , [ $this->getTbl() . '.' . $idName , '=', $this->getTblLang() . '.' . $idNameInLang ] )
                ->where_equal( $this->getTblLang() . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getActive()->id );
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

    public function findApi( $filter = [] , $order = '' , $limit = '' , $offset = '' )
    {
        if ( $order != '' )
        {
            $rst = $this->getKit( false );

            if ( substr( $order , 0 , 1 ) == '-' )
            {
                $field = substr( $order , 1 ) ;
                $rst = $rst->order_by_desc( $this->getEntity()->get( $field )->fieldSql() ) ;
            }
            else
            {
                $rst = $rst->order_by_asc( $this->getEntity()->get( $order )->fieldSql() ) ;
            }
        }
        else
        {
            $rst = $this->getKit();
        }

        if ( $limit != '' )
        {
            $rst = $rst->limit( $limit );
        }

        if ( $offset != '' )
        {
            $rst = $rst->offset( $offset );
        }

        if ( $filter )
        {
            foreach( $filter as $key => $value )
            {
                $rst = $rst->where_equal( $this->getEntity()->get( $key )->fieldSql() , $value ) ;
            }
        }

        return $rst->find_many();
    }

    public function getKit( $order = true )
    {
        $all = \DB::for_module( $this->getName() );
        if ( $this->getLimitGetAll() !== NULL ) $all = $all->limit( $this->getLimitGetAll() );

        if ( $this->getEntity()->hasValidation() ) 	$all = $all->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->fieldSql() , 1 );

        if ( $this->getEntity()->hasMultiLang() )
        {
            $idName			= \DB::getIdName( $this->getName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getName() ) ;

            $all = $all->left_outer_join( $this->getTblLang() , [ $this->getTbl() . '.' . $idName , '=', $this->getTblLang() . '.' . $idNameInLang ] )
                ->where_equal( $this->getTblLang() . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getActive()->id );
        }

        if ( $order )
        {
            if ( $this->getEntity()->hasOrder() ) 	$all = $all->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() );
            else									$all = $all->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );
        }

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