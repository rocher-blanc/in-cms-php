<?php

namespace App\Kernel\Back;

class Repository extends \App\Kernel\Common\Repository
{
    public function checkIfPatchTable( $id )
    {
        if ( file_exists( ENTITY_PATH . '/' . $this->getName() . '.php' ) )
        {
            $file = ENTITY_PATH . "/" . $this->getName() . ".php";
        }
        else if ( file_exists( V_ENTITY_PATH . '/' . $this->getName() . '.php' ) )
        {
            $file = V_ENTITY_PATH . "/" . $this->getName() . ".php";
        }

        if ( \App\Kernel\Container::getInstance()->param()->get('key_module_' . $id ) != md5_file( $file ) )
        {
            \DB::patchModuleTable( $this->getName() ) ;
            \App\Kernel\Container::getInstance()->param()->set('key_module_' . $id , md5_file( $file ) );
        }
    }

    public function findWhere( $where )
    {
        $tab = [];
        foreach( $where as $key => $value )
        {
            $tab[ $this->getEntity()->get( $key )->getColumn() ] = $value ;
        }

        return \DB::for_module( $this->getName() )->where( $tab )->find_one();
    }

    public function findOne( $id )
    {
        return \DB::for_module( $this->getName() )->where_id_is( $id )->find_one();
    }

    public function first()
    {
        return \DB::for_module( $this->getName() )->find_one();
    }

    public function getImage( $id )
    {
        return \DB::for_module( $this->getName() )->select($this->getEntity()->get( $this->getEntity()->getFirstImageName() )->getColumn() )->where_id_is( $id )->find_one();
    }

    public function minPosition( $array )
    {
        return \DB::for_module( $this->getName() )
            ->where_in( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() , $array )->min( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() );
    }

    public function findOneLang( $id , $idlang )
    {
        return \DB::for_module_lang( $this->getName() , $id , $idlang )->find_one() ;
    }

    public function createLang()
    {
        return \DB::for_module_lang( $this->getName() )->create();
    }

    public function create()
    {
        return \DB::for_module( $this->getName() )->create();
    }

    public function checkDatabase()
    {
        \DB::checkModuleTable( $this->getName() , \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity()->hasMultiLang() , \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity()->getField() ) ;
    }

    public function getAllTableIndex( $order , $by , $fields , $module_element_parent_id , $offset , $limit , $DepedencyModule = NULL , $DepedencyElement = NULL )
    {
        $content = $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule , $DepedencyElement );

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

    public function requestFirstGetAllTableIndex()
    {
        return \DB::for_module( $this->getName() );
    }

    public function requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule = NULL , $DepedencyElement = NULL )
    {
        $content = $this->requestFirstGetAllTableIndex();

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

        foreach( $fields as $field )
        {
            if ( ( $field['type'] == 'textarea' or $field['type'] == 'text' or $field['type'] == 'hidden' ) && !empty( $field['value'] ) )
            {
                $content = $content->where_like( $this->getEntity()->get( $field['name'] )->fieldSql() , '%' . $field['value'] . '%' ) ;
            }
            else if ( $field['type'] == 'date' && $field['value_convert_start'] !== NULL && $field['value_convert_end'] !== NULL )
            {
                $content = $content->where_date_gte( $this->getEntity()->get( $field['name'] )->fieldSql() , $field['value_convert_start'] )
                    ->where_date_lte( $this->getEntity()->get( $field['name'] )->fieldSql() , $field['value_convert_end'] );
            }
            else if ( $field['type'] == 'select' && !empty( $field['value'] ) )
            {
                $content = $content->where_equal( $this->getEntity()->get( $field['name'] )->fieldSql() , $field['value'] );
            }
            else if ( $field['type'] == 'radio' && !empty( $field['value'] ) )
            {
                if ( $field['value'] == -1 ) $field['value'] = 0;
                $content = $content->where_equal( $this->getEntity()->get( $field['name'] )->fieldSql() , $field['value'] );
            }
            else if ( $field['type'] == 'number' )
            {
                if ( ! empty( $field['value_start'] ) )
                {
                    $content = $content->where_gte( $this->getEntity()->get( $field['name'] )->fieldSql() , $field['value_start'] );
                }

                if ( ! empty( $field['value_end'] ) )
                {
                    $content = $content->where_lte( $this->getEntity()->get( $field['name'] )->fieldSql() , $field['value_start'] );
                }
            }
        }

        if ( $module_element_parent_id !== NULL && $this->getEntity()->isChild() )
        {
            $content = $content->where_equal( $this->getEntity()->get( $this->getEntity()->getModuleParentIdName() )->fieldSql() , $module_element_parent_id );
        }

        if ( $DepedencyModule !== NULL && $DepedencyElement !== NULL )
        {
            $content = $content->where_equal( $this->getEntity()->get( $this->getEntity()->getModuleIdName() )->fieldSql() , $DepedencyModule )
                               ->where_equal( $this->getEntity()->get( $this->getEntity()->getElementIdName() )->fieldSql() , $DepedencyElement );
        }

        if ( $order === NULL && $by === NULL or ( $by != 'desc' && $by != 'asc' ) )
        {
            if ( $this->getEntity()->hasOrder() )
            {
                $content = $content->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
            }
            else
            {
                $content = $content->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ;
            }
        }
        else
        {
            if ( $by == 'desc' )
            {
                $content = $content->order_by_desc( $this->getEntity()->get( $order )->getColumn() ) ;
            }
            else
            {
                $content = $content->order_by_asc( $this->getEntity()->get( $order )->getColumn() ) ;
            }
        }

        return $content ;
    }

    public function getParentContent( $module_element_parent_id = NULL )
    {
        $all = \DB::for_module( $this->getName() );
        $all->select( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );

        if ( $this->getEntity()->hasMultiLang() )
        {
            $idName			= \DB::getIdName( $this->getName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $this->getName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $this->getName() ) ;

            $all = $all->left_outer_join( $this->getTblLang() , [ $this->getTbl() . '.' . $idName , '=', $this->getTblLang() . '.' . $idNameInLang ] )
                ->where_equal( $this->getTblLang() . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getActive()->id );
        }

        if ( $module_element_parent_id !== NULL && $this->getEntity()->isChild() )
        {
            $all = $all->where_equal( $this->getEntity()->get( $this->getEntity()->getModuleParentIdName() )->fieldSql() , $module_element_parent_id );
        }

        if ( $this->getEntity()->hasImage() )
        {
            $all = $all->select( $this->getEntity()->get( $this->getEntity()->getFirstImageName() )->fieldSql() );
        }

        if ( $this->getEntity()->getFieldReference() )
        {
            foreach( $this->getEntity()->getFieldReference() as $ref )
            {
                $all = $all->select( $this->getEntity()->get( $ref )->fieldSql() );
            }
        }

        if ( $this->getEntity()->hasOrder() ) 	$all = $all->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() );
        else									$all = $all->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );

        return $all->find_many();
    }

	public function countWithParent( $parent_id )
	{
		return \DB::for_module( $this->getName() )
			->where_equal( $this->getEntity()->get( $this->getEntity()->getModuleParentIdName() )->fieldSql() , $parent_id )
			->count();
	}

	public function countWithDepedencyElement( $module , $element )
	{
		return \DB::for_module( $this->getName() )
			->where_equal( $this->getEntity()->get( $this->getEntity()->getModuleIdName() )->fieldSql() , $module )
			->where_equal( $this->getEntity()->get( $this->getEntity()->getElementIdName() )->fieldSql() , $element )
			->count();
	}
}