<?php

namespace App\Kernel\Back\User;

class Repository extends \App\Kernel\Back\Repository
{
    public function getAllTableIndex( $order , $by , $fields , $module_element_parent_id , $offset , $limit , $DepedencyModule = NULL , $DepedencyElement = NULL )
    {
        $content = $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule , $DepedencyElement )
            ->left_outer_join( 'user_front' , array( 'user_front.user_front_id' , '=', $this->getEntity()->get('user_front_id')->fieldSql() ) )
            ->select($this->getTbl() . '.*' )
            ->select_expr('user_front.user_front_login', $this->getEntity()->get('user_login')->getColumn());

        if ( $limit != 0 )
        {
            $content = $content->limit( $limit )->offset( $offset );
        }

        return $content->find_many();
    }

    public function countTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule = NULL , $DepedencyElement = NULL )
    {
        return $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule , $DepedencyElement )->count();
    }
}