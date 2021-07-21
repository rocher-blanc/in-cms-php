<?php

namespace App\Kernel\Back;

use App\Kernel\Container;
use App\Kernel\Debug;

class Repository extends \App\Kernel\Common\Repository
{
    /* ************************************************** */
    /* ******************   TOOLS    ******************** */
    /* ************************************************** */

    protected function Container()
    {
        return Container::getInstance();
    }

    /* ************************************************** */
    /* ****************** FUNCTIONS ********************* */
    /* ************************************************** */

    public function checkIfPatchTable( $id )
    {
        $class = $this->Container()->module( $this->getName() )->getEntityClassName() ;
        $files[] = _PATH_ . "" . str_replace( "\\" , "/" , $class ) ;
        $files[] = VENDOR_PATH . '/jwebcreation/cms' . str_replace( "\\" , "/" , $class ) ;
        $files[] = VENDOR_PATH . '/jwebcreation/jshop' . str_replace( "\\" , "/" , $class ) ;

        foreach( $files as $fileforeach )
        {
            if ( file_exists( $fileforeach . '.php' ) )
            {
                $file = $fileforeach . ".php";
            }
        }

        if ( $this->Container()->param()->get('key_module_' . $id ) != md5_file( $file ) )
        {
            \DB::patchModuleTable( $this->getName() ) ;
            $this->Container()->param()->set('key_module_' . $id , md5_file( $file ) );
        }
    }

    public function findWhere( $where )
    {
        $query = \DB::for_module( $this->getName() );

        $tab = [];
        foreach( $where as $key => $value )
        {
            if ( ! is_null( $value ) )
            {
                $tab[ $this->getEntity()->get( $key )->getColumn() ] = $value ;
            }
            else
            {
                $query = $query->where_null( $this->getEntity()->get( $key )->getColumn() );
            }
        }

        if ( ! empty( $tab ) )
        {
            $query = $query->where( $tab );
        }

        return $query->find_one();
    }

    public function findOne( $id )
    {
        return \DB::for_module( $this->getName() )->where_id_is( $id )->find_one();
    }

    public function findAll()
    {
        return \DB::for_module( $this->getName() )->find_many();
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

    public function maxPosition()
    {
        return \DB::for_module( $this->getName() )->max( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() );
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
        \DB::checkModuleTable( $this->getName() , Container::getInstance()->module( $this->getName() )->getEntity()->hasMultiLang() , Container::getInstance()->module( $this->getName() )->getEntity()->getField() ) ;
    }

    public function getAllTableIndex( $order , $by , $fields , $module_element_parent_id , $offset , $limit , $DepedencyModule = NULL , $DepedencyElement = NULL )
    {
        $content = $this->requestGetAllTableIndex( $order , $by , $fields , $module_element_parent_id , $DepedencyModule , $DepedencyElement );

        if ( $limit != 0 )
        {
            $content = $content->limit( $limit )->offset( $offset );
        }

        return$content->find_many();
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
            else if ( $field['type'] == 'checkbox' && ( $field['value'] !== '' && $field['value'] !== NULL ) )
            {
				$req = \DB::for_module_assoc( $this->getName() , $field['name'] )
					->select( \DB::getTableNameAssoc( $this->getName() , $field['name'] ) . '_' . \DB::getIdName( $this->getName() ), 'element_id' )
					->where_in( \DB::getTableNameAssocValue( $this->getName() , $field['name'] ) , $field['value'] )
					->find_many();

				if( $req )
				{
					$ids = [];
					foreach( $req as $row )
					{
						if( ! in_array( $row->element_id , $ids ) )
						{
							$ids[] = $row->element_id;
						}
					}

					$content->where_in( $this->getEntity()->get('id')->fieldSql() , $ids );
				}
            }
            else if ( $field['type'] == 'select' && ( $field['value'] !== '' && $field['value'] !== NULL ) )
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
                $content = $content->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() ) ;
            }
            else
            {
                $content = $content->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() ) ;
            }
        }
        else
        {
            if ( $by == 'desc' )
            {
                $content = $content->order_by_desc( $this->getEntity()->get( $order )->fieldSql() ) ;
            }
            else
            {
                $content = $content->order_by_asc( $this->getEntity()->get( $order )->fieldSql() ) ;
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

	public function duplicateCheckbox( $nameField , $id , $newId )
    {
        $rst = \DB::for_module_assoc( $this->getName() , $nameField )
            ->select( \DB::getTableNameAssocValue( $this->getName() , $nameField ) , 'value' )
            ->where_equal( \DB::getTableNameAssoc( $this->getName() , $nameField ) . '_' . \DB::getIdName( $this->getName() ) , $id )
            ->find_many();

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $check = \DB::for_module_assoc( $this->getName() , $nameField )->create();
                $check->set( \DB::getTableNameAssocValue( $this->getName() , $nameField ) , $row->value );
                $check->set( \DB::getTableNameAssoc( $this->getName() , $nameField ) . '_' . \DB::getIdName( $this->getName() ) , $newId );
                $check->save();
            }
        }
    }
}