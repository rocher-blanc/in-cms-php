<?php

namespace App\Kernel\Common;

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
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity() ;
    }

    public function create()
    {
        return \DB::for_module( $this->getName() )->create();
    }

	public function count()
	{
		return \DB::for_module( $this->getName() )->count();
	}

    public function findAllForSelect( $target , $alias , $parentName )
    {
        return \DB::find_all_for_select( $this->getName() , $target , $alias , \App\Kernel\Lang::getInstance()->getDefault()->id , $parentName ) ;
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
}