<?php

namespace App\Kernel\Front;

use App\Kernel\Container;

class Repository extends \App\Kernel\Common\Repository
{
    protected $_limit_get_all = NULL;

    public function getLimitGetAll()
    {
        return $this->_limit_get_all ;
    }

    public function findOne( $id )
    {
        /* Requete pour aller chercher les données */
        $result = \DB::for_module( $this->getName() )->where_id_is( $id );

        if ( $this->getEntity()->hasValidation() )
        {
            if ( ! ( ! empty( $_GET['id'] ) && ! empty( $_GET['preview'] ) && md5( $_GET['id'] ) == $_GET['preview'] ) )
            {
                $result = $result->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->fieldSql() , 1 );
            }
        }

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

    public function findAll( $currentPage = NULL )
    {
        $rst = $this->getKit() ;

        if ( $currentPage !== NULL )
        {
            $rst = $rst->limit( $this->getEntity()->getPagination() )
                ->offset( ( $this->getEntity()->getPagination() * $currentPage ) - $this->getEntity()->getPagination() );
        }

        return $rst->find_many();
    }

    #################################################################################################################################
    ##################################################    USER     ##################################################################
    #################################################################################################################################

    public function User()
    {
        return \App\Kernel\Front\User::getInstance() ;
    }

    public function findAllUser( $currentPage = NULL )
    {
        $rst = $this->getKit()->where_equal( $this->getEntity()->get('user_front_id')->getColumn() , $this->User()->getId() ) ;

        if ( $currentPage !== NULL )
        {
            $rst = $rst->limit( $this->getEntity()->getPagination() )
                ->offset( ( $this->getEntity()->getPagination() * $currentPage ) - $this->getEntity()->getPagination() );
        }

        return $rst->find_many();
    }

    #################################################################################################################################
    #################################################################################################################################
    #################################################################################################################################

	public function findNextElement( $currentId, $order = null, $type = null )
	{
        if($order)
        {
            $current_element = $this->getKit()
                ->where( $this->getEntity()->get('id')->getColumn() , $currentId )
                ->find_one();

            if($type === "DATE" or $type === "DATETIME") return $this->getKit()
                    ->where_date_gte_strict( $this->getEntity()->get($order)->getColumn() , $current_element->mod_event_date )
                    ->order_by_asc( $this->getEntity()->get($order)->getColumn() )
                    ->where_not_equal($this->getEntity()->get('id')->getColumn() , $currentId)
                    ->find_one();

            return $this->getKit()
                ->where_gt( $this->getEntity()->get($order)->getColumn() , $current_element->mod_event_date )
                ->order_by_desc( $this->getEntity()->get('id')->getColumn() )
                ->find_one();
        }
		return $this->getKit()
			->where_gt( $this->getEntity()->get('id')->getColumn() , $currentId )
			->find_one();
	}

	public function findPrevElement( $currentId, $order = null, $type = null )
	{
        if($order)
        {
            $current_element = $this->getKit()
                ->where( $this->getEntity()->get('id')->getColumn() , $currentId )
                ->find_one();

            if($type === "DATE" or $type === "DATETIME") return array_reverse($this->getKit()
                    ->where_date_lte_strict( $this->getEntity()->get($order)->getColumn() , $current_element->mod_event_date )
                    ->order_by_asc( $this->getEntity()->get($order)->getColumn() )
                    ->where_not_equal($this->getEntity()->get('id')->getColumn() , $currentId)
                    ->find_many())[0];

            return $this->getKit()
                ->where_lt( $this->getEntity()->get($order)->getColumn() , $current_element->mod_event_date )
                    ->order_by_desc( $this->getEntity()->get('id')->getColumn() )
                ->find_one();
        }
		return $this->getKit()
			->where_lt( $this->getEntity()->get('id')->getColumn() , $currentId )
			->order_by_desc( $this->getEntity()->get('id')->getColumn() )
			->find_one();
	}

    #################################################################################################################################
    #################################################################################################################################
    #################################################################################################################################

    public function findSiteMap( $field , $id )
    {
        $table 	= \DB::getTableName( $this->getName() ) ;
        $idName = \DB::getIdName( $this->getName() ) ;
        $return = \DB::for_module( $this->getName() )
            ->select( $table . '.' . $idName , "id" )
            ->select( $this->getEntity()->get('date_updated')->fieldSql() , 'date_updated' )
            ->select('seo.seo_url')
            ->select('seo.seo_lang_id')
            ->left_outer_join('seo', [ $table . '.' . $idName, '=', 'seo.seo_element_id' ])
            ->where_equal('seo.seo_module_id' , $id)
            ->where_equal('seo.seo_index' , 1)
            ->where_in('seo_lang_id', \App\Kernel\Lang::getInstance()->getTabLang() );

        if ( $this->getEntity()->hasValidation() )
        {
            $return = $return->where_equal( $table . '.' . $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , 1 );
        }

        if ( ! empty( $field ) )
        {
            foreach( $field as $row )
            {
                $return = $return->select( $row->fieldSql() );
            }
        }

        return $return->find_many();
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
        	// Il existe un champ avec un ordre par défaut
        	if( $this->getEntity()->getFieldOrderField() )
			{
				$method = 'order_by_' . strtolower( $this->getEntity()->getFieldOrderType() );
				$all = $all->$method( $this->getEntity()->get( $this->getEntity()->getFieldOrderField() )->fieldSql() );
			}
        	// L'ordre est activé sur les modules
            else if ( $this->getEntity()->hasOrder() )
			{
				$all = $all->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->fieldSql() );
			}
            // Aucun ordre n'est défini
            else
			{
				$all = $all->order_by_desc( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() );
			}
        }

        return $all;
    }

    public function getValid( $cats )
    {
        $rst = \DB::for_module( $this->getName() )
            ->select( $this->getEntity()->get( $this->getEntity()->getIdName() )->fieldSql() , 'id' );

        if ( $this->getEntity()->isChild() )
        {
            $rst = $rst->where_in( $this->getEntity()->get( $this->getEntity()->getModuleParentIdName() )->fieldSql() , $cats );
        }

        if ( $this->getEntity()->hasValidation() )
        {
            $rst = $rst->where_equal( $this->getEntity()->get( $this->getEntity()->getValidationName() )->fieldSql() , 1 );
        }

        return $rst->find_many();
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

    public function requestAll( array $request , $currentPage = NULL )
    {
        $rst = $this->request( $request ) ;

		if ( $currentPage !== NULL )
		{
			$pagination = $this->getEntity()->getPagination();

			if ( array_key_exists('pagination' , $request ) ) $pagination = $request['pagination'];

			$rst = $rst->limit( $pagination )
				->offset( ( $pagination * $currentPage ) - $pagination );
		}

        $rqt = $rst->find_many();

		return $rqt ;
    }

	public function requestOne( array $request )
	{
		return $this->request( $request )->find_one();
	}

	public function requestCount( array $request ):int
	{
		return $this->request( $request )->count();
	}

    public function request( array $request )
    {
        $order = true ;
        if ( array_key_exists( 'order' , $request ) ) $order = false ;

        $rst = $this->getKit( $order ) ;

        if ( ! array_key_exists( 'force' , $request ) || $request['force'] == false )
        {
            $cats = [] ;
            if ( $this->getEntity()->isChild() )
            {
                $tab        = [] ;
                $remontada  = true ;
                $parent     = $this->getEntity()->getModuleParentName() ;
                $tab[]      = $parent ;

                while( $remontada )
                {
                    $Entity = \App\Kernel\Container::getInstance()->module( $parent )->getEntity();
                    if ( $Entity->isChild() )
                    {
                        $parent = $Entity->getModuleParentName();
                        $tab[] = $parent ;
                    }
                    else
                    {
                        $remontada = false ;
                    }
                }

                $tab        = array_reverse( $tab );
                $arrCurrent = [];
                $cats       = [];

                foreach( $tab as $ent )
                {
                    $mod  = Container::getInstance()->module( $ent );
                    $nov  = $mod->getRepository()->getValid( $cats );
                    $cats = [];

                    if ( $nov )
                    {
                        foreach( $nov as $row )
                        {
                            $cats[ $row->get('id') ] = $row->get('id') ;
                        }
                    }
                }

                if ( ! empty( $cats ) )
                {
                    $rst->where_in( $this->field( $this->getEntity()->getModuleParentIdName() ) , $cats );
                }
            }
        }

        if ( ! empty( $request ) )
        {
            foreach( $request as $type => $rqt )
            {
                switch( $type )
                {
                    case "id" :
                        $rst->where_id_is( $rqt );
                        break;
                    case "default" :
                        $rst->where_equal( $this->field( $this->getEntity()->getDefaultName() ) , $rqt );
                        break;
                    case "user" :
                        $rst->where_equal( $this->field( $this->getEntity()->getUserIdName() ) , $rqt );
                    break;
                    case "parent" :
                        $rst->where_equal( $this->field( $this->getEntity()->getModuleParentIdName() ) , $rqt );
                        break;
                    case "module" :
                        $rst->where_equal( $this->field( $this->getEntity()->getModuleIdName() ) , $rqt );
                        break;
                    case "element" :
                        $rst->where_equal( $this->field( $this->getEntity()->getElementIdName() ) , $rqt );
                        break;
                    case "limit" :
                        $rst->limit( $rqt );
                    break;
                    case "offset" :
                        $rst->offset( $rqt );
                    break;
                    case "order" :
                        if ( $rqt == 'rand' )
                        {
                            $rst->order_by_rand();
                        }
                        else
                        {
                            list( $key , $by ) = explode( ":" , $rqt );
                            if ( $by == 'asc' )
                            {
                                $rst->order_by_asc( $this->field( $key ) );
                            }
                            else if ( $by == 'desc' )
                            {
                                $rst->order_by_desc( $this->field( $key ) );
                            }
                        }
                    break;
					case "where" :
                        $exp = explode( ";" , $rqt );

                        foreach( $exp as $row )
                        {
                            $array = explode(':', $row);
                            if ( count( $array ) == 3 )
                            {
                                list( $key , $value , $op ) = explode( ":" , $row );
                                switch ( $op )
                                {
                                    case "<" :
                                        $rst = $rst->where_lt( $this->field( $key ) , $value );
                                        break;
                                    case "<=" :
                                        $rst = $rst->where_lte( $this->field( $key ) , $value );
                                        break;
                                    case ">" :
                                        $rst = $rst->where_gt( $this->field( $key ) , $value );
                                        break;
                                    case ">=" :
                                        $rst = $rst->where_gte( $this->field( $key ) , $value );
                                        break;
                                    case "!=" :
                                        $rst = $rst->where_not_equal( $this->field( $key ) , $value );
                                        break;
                                    case "date" :
                                        $rst = $rst->where_date( $this->field( $key ) , $value );
                                        break;
                                    case "date_gte" :
                                        $rst = $rst->where_date_gte( $this->field( $key ) , $value );
                                        break;
                                    case "date_lte" :
                                        $rst = $rst->where_date_lte( $this->field( $key ) , $value );
                                        break;
                                    case "like" :
                                        $rst = $rst->where_like( $this->field( $key ) , $value );
                                        break;
                                    case "not_like" :
                                        $rst = $rst->where_not_like( $this->field( $key ) , $value );
                                        break;
                                    case "in" :
                                        $rst = $rst->where_in( $this->field( $key ) , explode( ',' , $value ) );
                                        break;
                                    case "notin" :
                                        $rst = $rst->where_not_in( $this->field( $key ) , explode( ',' , $value ) );
                                        break;
                                }
                            }
                            else
                            {
                                list( $key , $value ) = explode( ":" , $row );
                                switch ( $value )
                                {
                                    case "null" :
                                        $rst = $rst->where_null( $this->field( $key ) );
                                        break;
                                    default:
                                        $rst = $rst->where_equal( $this->field( $key ) , $value );
                                        break;
                                }
                            }
                        }
                    break;
                }
            }
        }

        return $rst ;
    }

    protected function field( $key )
    {
        return $this->getEntity()->get( $key )->fieldSql() ;
    }
}