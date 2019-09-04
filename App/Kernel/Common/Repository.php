<?php

namespace App\Kernel\Common;

use App\Kernel\Container;

class Repository
{
    public function __construct( $name )
    {
        $this->name = $name ;
    }

    public function getName()
    {
        return $this->name ;
    }

    public function getTblLang()
    {
        return \DB::getTableNameLang( $this->getName() ) ;
    }

    public function getTbl()
    {
        return \DB::getTableName( $this->getName() ) ;
    }

    public function getEntity()
    {
        return Container::getInstance()->module( $this->getName() )->getEntity() ;
    }

    public function getBackController()
    {
        return Container::getInstance()->module( $this->getName() )->getController(true) ;
    }

    public function create()
    {
        return \DB::for_module( $this->getName() )->create();
    }

	public function count()
	{
		return \DB::for_module( $this->getName() )->count();
	}

    public function findAllForSelect( $target , $alias , $parentName , $filter = NULL )
    {
        return \DB::find_all_for_select( $this->getName() , $target , $alias , \App\Kernel\Lang::getInstance()->getDefault()->id , $parentName , $filter ) ;
    }

    public function countWhere( $where )
    {
        $tab = [];
        foreach( $where as $key => $value )
        {
            $tab[ $this->getEntity()->get( $key )->getColumn() ] = $value ;
        }

        return \DB::for_module( $this->getName() )->where( $tab )->count();
    }

    public function findAllForSelect2( $alias = NULL , $arrayContent = [] )
    {
        if ( $alias === NULL ) $alias = 'titre' ;

        $lang = false ;
        if ( ! empty( $this->getEntity()->getFieldReference() ) )
        {
            $content = \DB::for_module( $this->getName() )
                ->select( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() , 'id' ) ;

            foreach( $this->getEntity()->getFieldReference() as $field )
            {
                if ( $this->getEntity()->get( $field )->hasLang() ) $lang = true ;
                $content = $content->select( $this->getEntity()->get( $field )->fieldSql() );
            }

            $content = $content->select_expr( $this->getEntity()->get( $field )->fieldSql() , $alias );

            if ( ! empty( $arrayContent ) )
            {
                $content = $content->whereIn( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() , $arrayContent );
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

            $rst = $content->find_many() ;

            if ( $rst )
            {
                $final = [];
                $asso  = [];
                foreach( $rst as $row )
                {
                    $rstTab = [];
                    foreach( $this->getEntity()->getFieldReference() as $field )
                    {
                        if ( $this->getEntity()->get( $field )->getType() == 'date' )
                        {
                            $value = $row->get($this->getEntity()->get( $field )->getColumn() );
                            $rstTab[] = (new \DateTime($value))->format( ( $this->getEntity()->get( $field )->getData('hour') ? 'd/m/Y - H:i' : 'd/m/Y' ) );
                        }
                        else if ( $this->getEntity()->get( $field )->getType() == 'select' && $this->getEntity()->get( $field )->isAssociated() == true )
                        {
                            $value = $row->get($this->getEntity()->get( $field )->getColumn() );

                            if ( ! array_key_exists( $field , $asso ) )
                            {
                                $asso[ $field ] = $this->getBackController()->getValueAssociated( $this->getEntity()->get( $field ) , 'array' );
                            }

                            $rstTab[] = $asso[ $field ][ $value ];
                        }
                        else
                        {
                            $rstTab[] = $row->get( $this->getEntity()->get( $field )->getColumn() );
                        }
                    }

                    $row->set( $alias , implode( " " , $rstTab ) );
                    $final[] = $row ;
                }

                return $final ;
            }
            else
            {
                return false ;
            }
        }
        else
        {
            throw new \App\Kernel\Exception("No Field Reference exist - Entity : " . $this->getName() ) ;
        }
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

    // Pour les checkbox dans le même module (systeme de table d'association)
    public function getAssocSimpleValue( $nameField , $id )
    {
        $content = \DB::for_module_assoc( $this->getName() , $nameField )
            ->select( \DB::getTableNameAssocValue( $this->getName() , $nameField ) )
            ->where_equal( \DB::getTableNameAssoc( $this->getName() , $nameField ) . '_' . \DB::getIdName( $this->getName() ) , $id )
            ->find_many();

        $result  = array() ;

        if ( $content )
        {
            foreach( $content as $row )
            {
                $result[] = $row->get( \DB::getTableNameAssocValue( $this->getName() , $nameField ) ) ;
            }
        }

        return $result ;
    }

    // Pour les checkbox dans le même module (systeme de table d'association)
    public function getAssocSimpleValueIndex( $nameField )
    {
        $field = \DB::getTableNameAssocValue( $this->getName() , $nameField ) ;
        $content = \DB::for_module_assoc( $this->getName() , $nameField )
            ->select( $field )
            ->group_by( $field )
            ->find_many();

        $result  = array() ;

        if ( $content )
        {
            foreach( $content as $row )
            {
                $result[] = $row->get( $field ) ;
            }
        }

        return $result ;
    }
}