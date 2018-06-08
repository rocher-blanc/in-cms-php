<?php

namespace App\Kernel\Entity;

class Field
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $value = NULL ;
    private $data = [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->setData( "tab" , "contenu" ) ;
        $this->setData( "front" , true ) ;
        $this->setData( "back" , true ) ;
    }

    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */

    public function setEntityName( $value )
    {
        $this->entity_name = $value ;
    }

    public function setName( $value )
    {
        $this->name = $value ;
        $this->setData( "columnName" , \DB::getColumnName( $this->getName() , $this->getEntityName() ) ) ;
        $this->setData( "fieldSql" ,  \DB::getTableName( $this->getEntityName() ) . "." . \DB::getColumnName( $this->getName() , $this->getEntityName() ) ) ;
    }

    public function setUnit( $value , $where )
    {
        $this->setData( "unit" , $value ) ;
        $this->setData( "whereUnit" , $where ) ;
    }

    public function setError( $value )
    {
        $this->setData( "error" , $value ) ;
    }

    public function setValue( $value , $lang = NULL )
    {
        if ( $lang === NULL ) 	$this->value = $value ;
        else					$this->value[ $lang ] = $value ;
    }

    public function setData( $key , $value )
    {
        $this->data[ $key ] = $value ;
    }

    public function setLang()
    {
        $this->setData( "lang" , true );
        $this->setData( "columnName" , \DB::getColumnName( $this->getName() , $this->getEntityName() , true ) ) ;
        $this->setData( "fieldSql" ,  \DB::getTableNameLang( $this->getEntityName() ) . "." . \DB::getColumnName( $this->getName() , $this->getEntityName() , true ) ) ;
    }

    public function setFormat( $name , $value )
    {
        if ( $this->getData('dateFormat') !== NULL )	$data = $this->getData('dateFormat');
        else											$data = [] ;

        $data[ $name ] = $value;
        $this->setData( "dateFormat" , $data );
    }

    public function setCrop( Array $tab )
    {
        if ( $this->getData('crop') !== NULL )	$data = $this->getData('crop');
        else									$data = [] ;

        $data[] = $tab;
        $this->setData( "crop" , $data );
    }

    public function setThumb( Array $tab )
    {
        if ( $this->getData('thumb') !== NULL )	$data = $this->getData('thumb');
        else									$data = [] ;

        $data[] = $tab;
        $this->setData( "thumb" , $data );
    }

    /* ************************************************** */
    /* ******************     ISER    ******************* */
    /* ************************************************** */

    public function isURL()
    {
        if ( $this->getData('isURL') === true ) 	return true ;
        else										return false ;
    }

    public function isRequired()
    {
        if ( $this->getData('notEmpty') === true ) 	return true ;
        else										return false ;
    }

    public function isSearch()
    {
        if ( $this->getData('search') === true ) 	return true ;
        else										return false ;
    }

    public function isParent()
    {
        if ( $this->getData('parent') === true ) 	return true ;
        else										return false ;
    }

    public function isFull()
    {
        if ( $this->getData('full') === true ) 	return true ;
        else										return false ;
    }

    public function isOrder()
    {
        if ( $this->getData('order') === true ) 	return true ;
        else										return false ;
    }

    public function isParentModule()
    {
        if ( $this->getData('moduleParent') === true ) 	return true ;
        else										        return false ;
    }

    public function isAssociated()
    {
        if ( $this->getData('manyToMany') === true or $this->getData('oneToMany') === true or $this->getData('manyToOne') === true or $this->getData('oneToOne') === true )
        {
            return true ;
        }
        else
        {
            return false ;
        }
    }

    public function isEmpty( $lang = NULL )
    {
        if ( is_string( $this->getValue( $lang ) ) )
        {
            if ( trim( $this->getValue( $lang ) ) === '' or $this->getValue( $lang ) === '' )   return true ;
            else                                                                                return false ;
        }
        else
        {
            if ( empty( $this->getValue( $lang ) ) )    return true ;
            else                                        return false ;
        }
    }

    /* ************************************************** */
    /* *****************     HASER    ******************* */
    /* ************************************************** */

	public function force()
	{
		if ( $this->getData('force') === true ) 	return true ;
		else									    	return false ;
	}

	public function save()
	{
		if ( $this->getData('nosave') === true ) 	return false ;
		else									    	return true ;
	}

	public function rename()
	{
		if ( $this->getData('norename') === true ) 	return false ;
		else									    	return true ;
	}

    public function canUpdate()
    {
        if ( $this->getData('noUpdate') === true ) 	return false ;
        else									    return true ;
    }

    public function hasLang()
    {
        if ( $this->getData('lang') === true ) 	return true ;
        else									return false ;
    }

    public function hasTwigKey()
    {
        if ( $this->getData('twig') != '' ) 	return true ;
        else									    return false ;
    }

    public function hasThumb()
    {
        if ( $this->getData('thumb') !== NULL ) return true ;
        else									return false ;
    }

    public function hasCrop()
    {
        if ( $this->getData('crop') !== NULL ) 	return true ;
        else									return false ;
    }

    public function hasFormat()
    {
        if ( $this->getData('dateFormat') !== NULL ) 	return true ;
        else											return false ;
    }

    public function hasOption()
    {
        if ( $this->getData('option') !== NULL && count( $this->getData('option') ) > 0 ) 	return true ;
        else									                                                    return false ;
    }

    public function hasOffeset()
    {
        if ( $this->getData('noOffset') === NULL ) 	return true ;
        else									    return false ;
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

	public function getTwigKey()
	{
		return $this->getData('twig') ;
	}

	public function front()
	{
		return $this->getData('front') ;
	}

	public function back()
	{
		return $this->getData('back') ;
	}

    public function getCrop()
    {
        return $this->getData('crop') ;
    }

    public function getTab()
    {
        return $this->getData('tab') ;
    }

    public function getThumb()
    {
        return $this->getData('thumb') ;
    }

    public function getFormat()
    {
        return $this->getData('dateFormat') ;
    }

    public function getObject()
    {
        return $this->getData('object') ;
    }

    public function getOptions()
    {
        return $this->getData('option') ;
    }

    public function getOption( $key )
    {
        return [
            "key" => $key,
            "value" => $this->getData('option')[ $key ]
        ];
    }

    public function getValue( $lang = NULL )
    {
        if ( $lang === NULL )
        {
            return $this->value ;
        }
        else if ( is_array( $this->value ) )
        {
            if ( array_key_exists( $lang , $this->value ) ) return trim( $this->value[ $lang ] ) ;
            else											return NULL ;
        }
        else
        {
            return NULL ;
        }
    }

    public function getFormatValue( $lang = NULL )
    {
        if ( $this->getType() == 'date' && $this->getValue() !== NULL )
        {
            list( $d , $m , $y ) = explode( '/' , $this->getValue( $lang ) ) ;
            $this->setValue("$y-$m-$d") ;
        }
        else if ( $this->getType() == 'radio' && $this->getData('isBoolean') == true )
        {
            $this->setValue( intval( $this->getValue( $lang ) ) ) ;
        }
    }

    public function getData( $key )
    {
        if ( array_key_exists( $key , $this->data ) ) 	return $this->data[ $key ] ;
        else											return NULL ;
    }

    public function getType()
    {
        return $this->getData('type') ;
    }

	public function getError()
	{
		return $this->getData('error');
	}

	public function getFrontError()
	{
		return $this->getData('front-error');
	}

    public function getDefault()
    {
        $rst = $this->getData('defaut');

        if ( is_callable( $rst ) )
		{
			return $rst();
		}

        return $rst ;
    }

    public function getColumn()
    {
        return $this->getData('columnName') ;
    }

    public function fieldSql()
    {
        return $this->getData('fieldSql') ;
    }

    public function getTitle()
    {
        return $this->getData('title') ;
    }

    public function getComment()
    {
        return $this->getData('comment') ;
    }

    public function getEntityName()
    {
        return $this->entity_name ;
    }

    public function getName()
    {
        return $this->name ;
    }

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    protected function Message()
    {
        return \App\Kernel\Message::getInstance() ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function checkEmpty()
    {
        $return = true ;

        if ( $this->hasLang() )
        {
            foreach( $this->Lang()->getAll() as $lang )
            {
                $this->setValue( $this->getApp()->request->post( $this->getColumn() . "_" . $lang->url ) , $lang->url ) ;

                if ( $this->isEmpty( $lang->url ) == true && $this->isRequired() == true )
                {
                    $this->setError( $this->getData('notEmpty_msg') ) ;
					$this->setData( 'front-error' , $this->getColumn() . '_empty' ) ;
                    $this->setValue( NULL , $lang->url ) ;
                    $return = false ;
                }
            }
        }
        else
        {
            if ( $this->rename() )  $this->setValue( $this->getApp()->request->post( $this->getColumn() ) ) ;
            else					$this->setValue( $this->getApp()->request->post( $this->getName() ) ) ;

            if ( $this->isEmpty() == true && $this->isRequired() == true )
            {
                $this->setError( $this->getData('notEmpty_msg') ) ;
				$this->setData( 'front-error' , ( $this->rename() ? $this->getColumn() : $this->getName() ) . '_empty' ) ;
                $this->setValue( NULL ) ;
                $return = false ;
            }

            if ( $this->getType() == 'image' && $this->isRequired() == true )
            {
                $Media = new \App\Kernel\Back\Media;
                $Media->setImageId( $this->getValue() ) ;

                if ( $Media->exist() == false )
                {
                    $this->setValue( NULL ) ;
                    $this->setError( $this->Message()->get('deletemedia_media_not_found') ) ;
                    $return = false ;
                }
            }

            if ( $this->getType() == 'link' )
            {
                if ( $this->getValue() != '' )
                {
                    $this->setValue( $this->getApp()->request->post( $this->getColumn() . "_type" ) . $this->getValue() ) ;
                }
            }

            if ( $this->isParent() == true && $this->getValue() == '' )
            {
                $this->setValue( NULL ) ;
            }
        }

        return $return ;
    }

    public function clearValue()
    {
        $this->setValue(NULL );
    }

    public function clearSqlInfos()
    {
        // $this->clearData( array("SQL_TYPE","SQL_VALUE","SQL_DEFAULT") ) ;
    }

    private function clearData( $field , $key )
    {
        if ( is_array( $key ) )
        {
            foreach( $key as $row )
            {
                unset( $this->data[ $field ][ $row ] ) ;
            }
        }
        else
        {
            unset( $this->data[ $field ][ $key ] ) ;
        }
    }
}