<?php

namespace App\Kernel\Factory;

class Form
{
	private function Lang()
	{
		return \App\Kernel\Lang::getInstance() ;
	}
	
	private function getApp()
	{
		return \Slim\Slim::getInstance() ;
	}
	
	public function genHTML( $field )
	{
		$html = "" ;
		
		if ( $field->getType() !== NULL )
		{
			if ( $field->hasLang() == true )
			{
				foreach( $this->Lang()->getAll() as $lang )
				{
					$html.= $this->genField( $field , $lang->url , $lang->flag ) ;
				}
			}
			else
			{
				$html.= $this->genField( $field ) ;
			}
		
			if ( $field->getError() !== NULL )
			{
				$html.= '<label for="id_' . $field->getColumn() . '" class="error">' . $field->getError() . '</label>' ;
			}
		}
		
		return $html ;
	}
	
	private function genField( $field , $lang = null , $flag = null )
	{
        if ( $lang !== null )   $name = $field->getColumn() . "_" . $lang ;
		else                    $name = $field->getColumn() ;

        if ( $flag !== null ) $field->setData('flag' , $flag );
		
		if ( $field->getValue() !== NULL )
		{
			if ( $lang !== null ) $value = $field->getValue( $lang ) ;
			else				  $value = $field->getValue() ;
		}
		else
		{
			$value = $field->getDefault() ;
		}
		
		$className = ucfirst( $field->getType() ) ;
		if ( file_exists( FORM_PATH . '/' . $className . '.php' ) )
		{
			$className = "\App\Kernel\Form\\" . $className ;
			$obj = new $className ;
			
			$html = $obj->html( $field , $name , $value );
			
			if ( method_exists( $obj , 'getLibCSS' ) ) $this->setLibCSS( $obj->getLibCSS() );
			if ( method_exists( $obj , 'getLibJS' ) ) $this->setLibJS( $obj->getLibJS() );
			
			return $html ;
		}
		else
		{
			throw new \App\Kernel\Exception("No PHP class for field type: " . $field->getType() . ' (field: ' . $field->getName() . ') ');
		}
	}
	
	private function setLibJS( $var )
	{
		if ( is_string( $var ) )
		{
			$this->_lib_js[ md5( $var ) ] = $var ;
		}
		else if ( is_array( $var ) )
		{
			foreach( $var as $row )
			{
				$this->_lib_js[ md5( $row ) ] = $row ;
			}
		}
	}
	
	private function setLibCSS( $var )
	{
		if ( is_string( $var ) )
		{
			$this->_lib_css[ md5( $var ) ] = $var ;
		}
		else if ( is_array( $var ) )
		{
			foreach( $var as $row )
			{
				$this->_lib_css[ md5( $row ) ] = $row ;
			}
		}
	}
	
	public function getLibCSS()
	{
		$html = '' ;
		if ( !empty( $this->_lib_css ) )
		{
			foreach( $this->_lib_css as $row )
			{
                if ( !empty( $row ) ) $html.= '<link rel="stylesheet" href="' . $this->site( $row ) . ( DEBUG ? '?' . time() : '' ) . '" />' . "\n" ;
			}
		}
		return $html ;
	}
	
	public function getLibJS()
	{
		$html = '' ;
		if ( !empty( $this->_lib_js ) )
		{
			foreach( $this->_lib_js as $row )
			{
                if ( !empty( $row ) ) $html.= '<script src="' . $this->site( $row ) . ( DEBUG ? '?' . time() : '' ) . '"></script>' . "\n" ;
			}
		}
		return $html ;
	}
	
	public function initLib()
	{
		$this->_lib_js  = '' ;
		$this->_lib_css = '' ;
	}
	
	public function site( $url )
    {
        return $this->getApp()->request()->getUrl() . '/assets/vendor/' . ltrim($url, '/');
    }
}