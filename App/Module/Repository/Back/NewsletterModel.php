<?php

namespace App\Module\Repository\Back;

use \App\Kernel\Back\Repository;

class NewsletterModel extends Repository
{
    public function findAllForSelect( $target , $alias , $parentName , $filter = NULL )
    {
        return \DB::find_all_for_select( $this->getName() , $target , $alias , \App\Kernel\Lang::getInstance()->getDefault()->id , $parentName , function( $c ) {
            $exp = explode('/' , $_SERVER['REQUEST_URI'] );
            return $c->where_equal( $this->getEntity()->get('element_module_parent_id')->fieldSql() , end( $exp ) );
        }) ;
    }
}