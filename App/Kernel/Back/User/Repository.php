<?php

namespace App\Kernel\Back\User;

class Repository extends \App\Kernel\Back\Repository
{
    public function getAllTableIndex( $order , $by , $fields , $module_element_parent_id , $offset , $limit , $DepedencyModule = NULL , $DepedencyElement = NULL )
    {
        $content = $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule , $DepedencyElement )
            ->left_outer_join( 'user_front' , array( 'user_front.user_front_id' , '=', $this->getEntity()->get('user_front_id')->fieldSql() ) )
            ->select($this->getTbl() . '.*' )
            ->select_expr('user_front.user_front_login', $this->getEntity()->get('user_login')->getColumn())
            ->select_expr('user_front.user_front_user_front_group_id', $this->getEntity()->get('user_front_user_front_group_id')->getColumn())
            ->select_expr('user_front.user_front_active', $this->getEntity()->get('isValid')->getColumn());

        if ( $limit != 0 )
        {
            $content = $content->limit( $limit )->offset( $offset );
        }

        return $content->find_many();
    }

    public function countTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule = NULL , $DepedencyElement = NULL )
    {
        return $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule , $DepedencyElement )
            ->left_outer_join( 'user_front' , array( 'user_front.user_front_id' , '=', $this->getEntity()->get('user_front_id')->fieldSql() ) )
            ->count();
    }

    public function findOne( $id )
    {
        return \DB::for_module( $this->getName() )->where_id_is( $id )
            ->left_outer_join( 'user_front' , array( 'user_front.user_front_id' , '=', $this->getEntity()->get('user_front_id')->fieldSql() ) )
            ->select($this->getTbl() . '.*' )
            ->select_expr('user_front.user_front_login', $this->getEntity()->get('user_login')->getColumn())
            ->select_expr('user_front.user_front_user_front_group_id', $this->getEntity()->get('user_front_user_front_group_id')->getColumn())
            ->select_expr('user_front.user_front_active', \DB::getColumnName('isValid', $this->getName() ))
            ->find_one();
    }

    public function findAllForSelect2( $alias = NULL , $filter = NULL )
    {
        if ( $alias === NULL ) $alias = 'titre' ;

        $lang = false ;
        if ( ! empty( $this->getEntity()->getFieldReference() ) )
        {
            $content = \DB::for_module( $this->getName() )
                ->select( $this->getEntity()->get('user_front_id')->fieldSql() , 'id' );

            $ct = count( $this->getEntity()->getFieldReference() );

            if ( $ct == 1 )
            {
                if ( $this->getEntity()->get( $this->getEntity()->getFieldReference()[0] )->isUser() && defined('MODULE_USER') )
                {
                    return \App\Kernel\Container::getInstance()->module( MODULE_USER )->getRepository(true)->findAllForSelect2();
                }

                if ( $this->getEntity()->get( $this->getEntity()->getFieldReference()[0] )->hasLang() ) $lang = true ;
                $content = $content->select( $this->getEntity()->get( $this->getEntity()->getFieldReference()[0] )->fieldSql() , $alias );
            }
            else
            {
                $i = 0;
                $str = "CONCAT(";
                foreach( $this->getEntity()->getFieldReference() as $field )
                {
                    if ( $this->getEntity()->get( $field )->hasLang() ) $lang = true ;

                    if ( $i > 0 ) $str.= ",' ',"; ;
                    $str.= $this->getEntity()->get( $field )->fieldSql() ;
                    $i++;
                }

                $str.= ")" ;
                $content = $content->select_expr( $str , $alias );
            }

            if ( $lang )
            {
                $tableLang  	= \DB::getTableNameLang( $this->getName() ) ;
                $idNameInLang	= \DB::getIdNameInLang( $this->getName() ) ;
                $langIdLangName	= \DB::getLangIdLangName( $this->getName() ) ;

                $content = $content->left_outer_join( $tableLang , array( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() , '=', $tableLang . '.' . $idNameInLang ))
                    ->where_equal( $tableLang . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getDefault()->id ) ;
            }

            if ( $this->getEntity()->getParentName() !== NULL )
            {
                $content = $content->select( $this->getEntity()->get( $this->getEntity()->getParentName() )->fieldSql() , $this->getEntity()->getParentName() );
            }

            if ( $this->getEntity()->hasOrder() ) 	$content = $content->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() );
            else									$content = $content->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );

            if ( is_callable( $filter ) )
            {
                $content = $filter( $content );
            }

            return $content->find_many() ;
        }
        else
        {
            throw new \App\Kernel\Exception("No Field Reference exist - Entity : " . $this->getName() ) ;
        }
    }
}