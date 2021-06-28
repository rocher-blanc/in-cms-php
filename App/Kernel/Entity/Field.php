<?php

namespace App\Kernel\Entity;

use App\Kernel\Back\Gallery;
use App\Kernel\Back\Media;

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
	/* ****************    TOOLS      ******************* */
	/* ************************************************** */

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
		else											    $data = [] ;

		$data[ $name ] = $value;
		$this->setData( "dateFormat" , $data );
	}

	public function setCrop( Array $tab )
	{
		if ( $this->getData('crop') !== NULL )	$data = $this->getData('crop');
		else									    $data = [] ;

		$data[] = $tab;
		$this->setData( "crop" , $data );
	}

	public function setThumb( Array $tab )
	{
		if ( $this->getData('thumb') !== NULL )	$data = $this->getData('thumb');
		else									        $data = [] ;

		$data[] = $tab;
		$this->setData( "thumb" , $data );
	}

	public function setCover( Array $tab )
	{
		if ( $this->getData('cover') !== NULL )	$data = $this->getData('cover');
		else									        $data = [] ;

		$data[] = $tab;
		$this->setData( "cover" , $data );
	}

	public function setWidth( Int $width )
	{
		$data = $this->getData('imgWidth') !== NULL
			? $this->getData('imgWidth')
			: [];

		$data[] = $width;
		$this->setData( "imgWidth" , $data );
	}

	public function setHeight( Int $height )
	{
		$data = $this->getData('imgHeight') !== NULL
			? $this->getData('imgHeight')
			: [];

		$data[] = $height;
		$this->setData( "imgHeight" , $data );
	}

	/* ************************************************** */
	/* ******************     ISER    ******************* */
	/* ************************************************** */

    public function isUser()
    {
        if ( $this->getData('user') === true ) 	    return true ;
        else										    return false ;
    }

    public function isCustom()
    {
        if ( $this->getData('custom') === true ) 	return true ;
        else										    return false ;
    }

	public function isURL()
	{
		if ( $this->getData('isURL') === true ) 	return true ;
		else										    return false ;
	}

	public function isRequired()
	{
		if ( $this->getData('notEmpty') === true ) 	return true ;
		else										    return false ;
	}

	public function isSearch()
	{
		if ( $this->getData('search') === true ) 	return true ;
		else										    return false ;
	}

	public function isParent()
	{
		if ( $this->getData('parent') === true ) 	return true ;
		else										    return false ;
	}

	public function isFull()
	{
		if ( $this->getData('full') === true ) 	return true ;
		else										return false ;
	}

	public function isOrder()
	{
		if ( $this->getData('order') === true ) 	return true ;
		else										    return false ;
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

	public function isListing()
	{
		return $this->getData('listing');
	}

	public function isEmpty( $lang = NULL )
	{
        if ( $this->getData('isBoolean') == true )
        {
            if ( trim( $this->getValue( $lang ) ) === '0' or $this->getValue( $lang ) === '' )   return true ;
            else                                                                                 return false ;
        }
        else if ( is_string( $this->getValue( $lang ) ) )
        {
            if ( trim( $this->getValue( $lang ) ) === '' or $this->getValue( $lang ) === '' )   return true ;
            else                                                                                return false ;
        }
        else if ( is_array( $this->getValue( $lang ) ) && $this->getType() == "document" )
        {
            if ( empty( $this->getValue( $lang ) ) ) return true ;
            else                                     return false ;
        }
        else
		{
			if ( empty( $this->getValue( $lang ) ) )    return true ;
			else                                        return false ;
		}
	}

	public function isUniq()
	{
		return (bool)( $this->getData('uniq') );
	}

	public function isFormated( $lang = NULL )
	{
		if ( is_callable( $this->getData('formated') ) )
		{
			$function = $this->getData('formated') ;

			return $function( $this->getValue( $lang ) );
		}
		else
		{
			return true ;
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
		else									        return true ;
	}

	public function hasLang()
	{
		if ( $this->getData('lang') === true ) 	return true ;
		else									    return false ;
	}

	public function hasTwigKey()
	{
		if ( $this->getData('twig') != '' ) 	return true ;
		else									    return false ;
	}

	public function hasThumb()
	{
		if ( $this->getData('thumb') !== NULL ) return true ;
		else									     return false ;
	}

	public function hasCover()
	{
		if ( $this->getData('cover') !== NULL ) return true ;
		else									     return false ;
	}

	public function hasCrop()
	{
		if ( $this->getData('crop') !== NULL ) 	return true ;
		else									    return false ;
	}

	public function hasWidth()
	{
		return $this->getData('imgWidth') !== NULL;
	}

	public function hasHeight()
	{
		return $this->getData('imgHeight') !== NULL;
	}

	public function hasFormat()
	{
		if ( $this->getData('dateFormat') !== NULL ) 	return true ;
		else											    return false ;
	}

	public function hasOption()
	{
		if ( $this->getData('option') !== NULL && count( $this->getData('option') ) > 0 ) 	return true ;
		else									                                                    return false ;
	}

	public function hasOffeset()
	{
		if ( $this->getData('noOffset') === NULL ) 	return true ;
		else									        return false ;
	}

	public function hasCondition()
	{
        if ( $this->getData('showIf') === NULL ) 	return false ;
        else									        return true ;
	}

	/* ************************************************** */
	/* ****************     GETTER    ******************* */
	/* ************************************************** */

	public function getMode()
	{
		return $this->getData('mode') ;
	}

	public function getTwigKey()
	{
		return $this->getData('twig') ;
	}

    public function front()
    {
        return $this->getData('front') ;
    }

    public function visible()
    {
        if ( $this->getData('visibility') === NULL ) 	return false ;
        else									            return true ;
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

	public function getGroup()
	{
		return $this->getData('group') ;
	}

	public function getThumb()
	{
		return $this->getData('thumb') ;
	}

	public function getCover()
	{
		return $this->getData('cover') ;
	}

	public function getWidth()
	{
		return $this->getData('imgWidth') ;
	}

	public function getHeight()
	{
		return $this->getData('imgHeight') ;
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
		return ( is_array( $this->getData('option') ) ? $this->getData('option') : [] ) ;
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
			if ( $this->getData('hour') == true )
            {
                list( $date , $hour ) = explode( ' - ' , $this->getValue( $lang ) ) ;
                list( $d , $m , $y ) = explode( '/' , $date ) ;
                list( $h , $i ) = explode( ':' , $hour ) ;
                $this->setValue("$y-$m-$d $h:$i:00") ;
            }
            else
            {
                list( $d , $m , $y ) = explode( '/' , $this->getValue( $lang ) ) ;
                $this->setValue("$y-$m-$d") ;
            }
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
		return $this->getData('type');
	}

	public function getSubType()
	{
		if( ! empty( $this->getData('subtype') ) )
		{
			return $this->getData('subtype');
		}
		else
		{
			return $this->getData('type');
		}
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

	public function getDefaultSearch()
	{
		return $this->getData('defautSearch'); ;
	}

	public function getUnit()
	{
		return ( ! empty( $this->getData('unit') ) )
			? [
				'unit'  => $this->getData('unit'),
				'where' => $this->getData('whereUnit'),
			]
			: false;
	}

	public function show( &$content )
	{
	    if ( $this->getType() == 'hidden' ) return true ;

		$rst = $this->getData('showIf');

		if ( is_callable( $rst ) )
		{
			return $rst( $content );
		}

		return true;
	}

	public function transform( $idlang = NULL )
	{
		$rst = $this->getData('transform');

		if ( is_callable( $rst ) )
		{
			$this->setValue( $rst( $this->getValue( $idlang ) ) , $idlang );
		}
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

	/* ************************************************** */
	/* ****************    IMPORT     ******************* */
	/* ************************************************** */

	public function checkImport( $value )
	{
		$this->setValue( $value ) ;

		if ( $this->isEmpty() == true && $this->isRequired() == true )
		{
			$this->setError( $this->getData('notEmpty_msg') ) ;
			$this->setValue( NULL ) ;

			return false ;
		}
		else
		{
			return $this->formatImportIsValid();
		}
	}

	protected function formatImportIsValid()
	{
		switch( $this->getType() )
		{
			case "number" :
				if ( is_numeric( $this->getValue() ) )
				{
					return true ;
				}
				else
				{
					$this->setError("Le champ doit être un entier") ;
					return false ;
				}
				break;
			case "radio" :
				if ( $this->getData('isBoolean') == true )
				{
					if ( $this->getValue() == 1 or $this->getValue() == 0 )
					{
						return true ;
					}
					else
					{
						$this->setError("Le champ doit contenir 0 ou 1") ;
						return false ;
					}
				}
				else
				{
					return true ;
				}
				break;
			case "image" :
				if ( @file_get_contents( $this->getValue() ) !== false )
				{
					$size = getimagesize( $this->getValue() );
					$rst  = (strtolower( substr( $size['mime'] , 0, 5 ) ) == 'image' ? true : false );

					if ( ! $rst )
					{
						$this->setError("Le champ doit être une URL d'image correct") ;
						return false ;
					}
					else
					{
						return true ;
					}
				}
				else
				{
					$this->setError("L'image \"" . $this->getValue() . "\" n'existe pas") ;
					return false ;
				}
				break;
			default :
				return true ;
				break;
		}
	}

	public function parseWithImport( $value , $mId , $mName )
	{
		switch( $this->getType() )
		{
			case "date" :
				$hour = NULL ;
				if ( strpos( $value , ' ' ) !== false )
				{
					$exp = explode( ' ' , $value );
					$date = $exp[0];
                    if ( $this->getData('hour') == true )
                    {
                        if ( preg_match("/^(\d{2}):(\d{2}):(\d{2})$/", $exp[1], $matches) )
                        {
                            $hour = $exp[1];
                        }
                    }
				}
				else
				{
					$date = $value;
				}

				if ( preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches) )
				{
					$this->setValue( $date . ( $hour !== NULL ? " $hour" : "" ) ) ;
				}
				else if( preg_match("/^(\d{2})\/(\d{2})\/(\d{4})$/", $date, $matches) )
				{
					list( $d, $m, $y ) = explode( '/' , $date );
					$this->setValue("$y-$m-$d" . ( $hour !== NULL ? " $hour" : "" ) ) ;
				}
				else if( preg_match("/^(\d{1})\/(\d{2})\/(\d{4})$/", $date, $matches) )
				{
					list( $d, $m, $y ) = explode( '/' , $date );
					$this->setValue("$y-$m-0$d" . ( $hour !== NULL ? " $hour" : "" ) ) ;
				}
				else if( preg_match("/^(\d{2})\/(\d{1})\/(\d{4})$/", $date, $matches) )
				{
					list( $d, $m, $y ) = explode( '/' , $date );
					$this->setValue("$y-0$m-$d" . ( $hour !== NULL ? " $hour" : "" ) ) ;
				}
				else if( preg_match("/^(\d{1})\/(\d{1})\/(\d{4})$/", $date, $matches) )
				{
					list( $d, $m, $y ) = explode( '/' , $date );
					$this->setValue("$y-0$m-0$d" . ( $hour !== NULL ? " $hour" : "" ) ) ;
				}
				break;
			case "image" :
				if ( !empty( $value ) )
				{
					$Media = new Media;
					$Media->setModuleName( $mName );
					$Media->setModuleId( $mId );
					$Media->setFolder( \App\Kernel\Container::getInstance()->module( $mName )->getEntity()->getFolder() );
					$this->setValue( $Media->createByUrl( $value , $this->getName() ) );
				}
				break;
			default :
				$this->setValue( $value ) ;
				break;
		}
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
				else if ( $this->isFormated( $lang->url ) == false )
				{
					$this->setError( $this->getData('notFormated_msg') ) ;
					$this->setData( 'front-error' , $this->getColumn() . '_formated' ) ;
					$return = false ;
				}
				else
				{
					$this->transform( $lang->locale );
				}
			}
		}
		else
		{
			if ( $this->rename() )  $key = $this->getColumn() ;
			else					$key = $this->getName() ;

            $this->setValue( $this->getApp()->request->post( $key ) ) ;

            if ( isset( $_FILES[ $key ] ) && !empty( $_FILES[ $key ]['tmp_name'] ) && $this->getType() == 'document' )
            {
                $this->setValue( $_FILES[ $key ] ) ;
            }
            else if ( isset( $_FILES[ "upload_" . $key ] ) && !empty( $_FILES[ "upload_" . $key ]['tmp_name'] ) && $this->getType() == 'image' )
            {
                if ( $this->isFormatImage( $_FILES[ "upload_" . $key ] ) )
                {
                    $this->setValue( $_FILES[ "upload_" . $key ] ) ;
                }
            }

            if ( $this->isEmpty() == true && $this->isRequired() == true && $this->getType() == 'gallery' )
            {
                $Gal = new Gallery;
                $Gal->setElementId( $this->getData('id') == '' ? -1 : $this->getData('id') );
                $Gal->setModuleId( $this->getData('entity_id') );
                $Gal->setField( $this->getName() );
                $ct = $Gal->count();

                if ( $ct == 0 )
                {
                    $this->setError( $this->getData('notEmpty_msg') ) ;
                    $this->setData( 'front-error' , ( $this->rename() ? $this->getColumn() : $this->getName() ) . '_empty' ) ;
                    $this->setValue( NULL ) ;
                    $return = false ;
                }
            }
            else if ( $this->isEmpty() == true && $this->isRequired() == true )
            {
                $this->setError( $this->getData('notEmpty_msg') ) ;
                $this->setData( 'front-error' , ( $this->rename() ? $this->getColumn() : $this->getName() ) . '_empty' ) ;
                $this->setValue( NULL ) ;
                $return = false ;
            }
            else if ( $this->isEmpty() == false && $this->isFormated() == false )
			{
				$this->setError( $this->getData('notFormated_msg') ) ;
				$this->setData( 'front-error' , $this->getColumn() . '_formated' ) ;
				$return = false ;
			}
			else
			{
				$this->transform();
			}

			if ( $this->getType() == 'image' && $this->isRequired() == true )
			{

				if ( is_array( $this->getValue() ) )
                {
                    if ( empty( $this->getValue()['name'] ) )
                    {
                        $this->setValue( NULL ) ;
                        $this->setError( $this->getData('notEmpty_msg') ) ;
                        $this->setData( 'front-error' , ( $this->rename() ? $this->getColumn() : $this->getName() ) . '_empty' ) ;
                        $return = false ;
                    }
                }
                else
                {
                    $Media = new Media;
                    $Media->setImageId( $this->getValue() ) ;

                    if ( $Media->exist() == false )
                    {
                        $this->setValue( NULL ) ;
                        $this->setError( $this->Message()->get('deletemedia_media_not_found') ) ;
                        $return = false ;
                    }
                }
			}
			else if ( $this->getType() == 'link' )
			{
				if ( $this->getValue() != '' )
				{
					$this->setValue( $this->getApp()->request->post( $this->getColumn() . "_type" ) . $this->getValue() ) ;
				}
			}
            else if ( $this->getType() == 'document' && is_array( $this->getValue() ) && !empty( $this->getValue() ) )
            {
                $this->setValue( implode( ',' , $this->getValue() ) ) ;
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

	private function isFormatImage( $file )
    {
        $allowedMimes = ["image/gif","image/jpeg","image/jpg","image/png"];
        $allowedExts = array("gif", "jpeg", "jpg", "png");

        $extension = end(explode(".", $file["name"] ) );

        if ( in_array( $file["type"] , $allowedMimes ) && in_array( $extension , $allowedExts ) )
        {
            return true ;
        }

        return false ;
    }
}