<?php

namespace App\Kernel\Entity;

class Model
{
	/*
     * @int
     * Variable contenant l'ID
     */
    public $_id = NULL ;
	
	protected $_action = "" ;
	
	protected $_setting = [] ;
	
	/* ************************************************** */
	/* ******************   SETTER   ******************** */
	/* ************************************************** */
    
    protected function setId( $id )
	{
        $this->_id = $id ;
    }
	
	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */
    
	/*
	 * @return $app de Slim
	 */
    public function getApp()
	{
        return \Slim\Slim::getInstance() ;
    }
	
	protected function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}
	
	public function getId()
	{
        return $this->_id ;
    }
	
	protected function getAction()
	{
        return $this->_action ;
    }

	public function getPathImage( $absolute = true )
	{
		$path = IMAGE_PATH . '/' . $this->getFolder() ;

		if ( $absolute ) return $path ;
		else			 return str_replace( WEB_PATH , '' , $path ) ;
	}

	public function getPathDocument( $absolute = true )
	{
		$path = DOCUMENT_PATH . '/' . $this->getFolder() ;

		if ( $absolute ) return $path ;
		else			 return str_replace( WEB_PATH , '' , $path ) ;
	}
    
    public function getClassName( $lower = true )
	{
        $exp = explode( "\\" , get_class( $this ) ) ;
		$content = $exp[ count( $exp ) - 1 ] ;
		
		if ( $lower )	return strtolower( $content ) ;
		else			return $content ;
    }

	/* ************************************************** */
	/* ******************  SETTINGS  ******************** */
	/* ************************************************** */
	
	protected function setDefaultSetting()
	{
		$this->_setting = array(
			"action" => array(
				'index',
				'table',
                'import',
                'export',
                'customization',
				'add',
				'edit',
				'delete'
			)
		);
	}
	
	/* ************************************************** */
	/* ******************   CHECK   ********************* */
	/* ************************************************** */
	
	protected function check()
	{
        if ( $this->hasUrl() == true )
        {
            $this->setLast( $this->getUrlName() ) ;
            if ( $this->hasMultilang() == true && $this->field()->hasLang() == false )
            {
                throw new \App\Kernel\Exception("Field \"" . $this->getUrlName() . "\" is not multilang. Please, update this field") ;
            }
        }

		if ( $this->hasImage() == true )
		{
            if ( !is_dir( $this->getPathImage() ) )
            {
                mkdir( $this->getPathImage() , 0755 );
				mkdir( $this->getPathImage() . '/c' , 0755 ); // Crope
				mkdir( $this->getPathImage() . '/t', 0755 ); // Thumb
			}
		}

		if ( $this->hasDocument() == true )
		{
			if ( !is_dir( $this->getPathDocument() ) )
			{
				mkdir( $this->getPathDocument() , 0755 );
			}
		}

		if ( $this->getMaxElement() == 1 && $this->canDelete() == false && $this->itsDepedency() == true )
        {
            $this->addAction('form');
        }

        if ( in_array( 'editor' , $this->_setting["action"] ) )
        {
            if ( !is_dir( $this->getPathImage() . '/e' ) )
            {
                if ( !is_dir( $this->getPathImage() ) )
                {
                    mkdir( $this->getPathImage() , 0755 );
                }

                $rst = mkdir( $this->getPathImage() . '/e' , 0755 );

                if ( $rst === false )
                {
                    throw new \App\Kernel\Exception("Creating folder is broken  \"" . $this->getPathImage() . "/e\"") ;
                }
            }

        }
	}
	
	/* ************************************************** */
	/* ******************   ACTION   ******************** */
	/* ************************************************** */

    public function addAction( $action )
	{
        if ( ! in_array( $action , $this->_setting["action"] ) ) $this->_setting["action"][ $action ] = $action ;
	}

    public function removeAction( $action )
	{
		if ( !empty( $this->_setting["action"] ) )
		{
			$array = [];
			foreach( $this->_setting["action"] as $row )
			{
				if ( $row != $action ) $array[ $row ] = $row ;
			}
		}
		$this->_setting["action"] = $array ;
	}
	
	public function hasAction( $action )
	{
		return in_array( $action , $this->_setting["action"] ) ;
	}
}