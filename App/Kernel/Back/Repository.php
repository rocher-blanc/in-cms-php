<?php

namespace App\Kernel\Back;

class Repository extends \App\Kernel\Common\Repository
{
    public function checkIfPatchTable( $id )
    {
        $file = ENTITY_PATH . "/" . $this->getName() . ".php";

        if ( \App\Kernel\Container::getInstance()->param()->get('key_module_' . $id ) != md5_file( $file ) )
        {
            \DB::patchModuleTable( $this->getName() ) ;
            \App\Kernel\Container::getInstance()->param()->set('key_module_' . $id , md5_file( $file ) );
        }
    }

    public function findOne( $id )
    {
        return \DB::for_module( $this->getName() )->where_id_is( $id )->find_one();
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

    public function createLang()
    {
        return \DB::for_module_lang( $this->getName() )->create();
    }

    public function checkDatabase()
    {
        \DB::checkModuleTable( $this->getName() , \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity()->hasMultiLang() , \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity()->getField() ) ;
    }

    public function getAllTableIndex( $order , $by , $fields , $module_element_parent_id , $offset , $limit )
    {
        $content = $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id );

        if ( $limit != 0 )
        {
            $content = $content->limit( $limit )->offset( $offset );
        }

        return $content->find_many();
    }

    public function countTableIndex( $order , $by , $fields , $module_element_parent_id )
    {
        return $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id )->count();;
    }

    public function requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id )
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

    public function getOnIndex( $id_module )
    {
        $tbl    = \DB::getTableName( $this->getName() );
        $idName	= \DB::getIdName( $this->getName() ) ;

        if ( ! $this->getEntity()->get( $this->getEntity()->getUrlName() )->hasLang() )   $tblField = \DB::getTableName( $this->getName() );
        else                                                                              $tblField = \DB::getTableNameLang( $this->getName() );

        $seo_module_one = \DB::for_module( $this->getName() )
            ->select( 'seo.seo_title' )
            ->select( 'seo.seo_description' )
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
            ->where_raw("(seo.seo_title IS NULL OR seo.seo_description IS NULL)",[])
            ->group_by('seo.seo_element_id')
            ->find_many();
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