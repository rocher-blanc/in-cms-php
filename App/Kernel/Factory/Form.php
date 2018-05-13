<?php

namespace App\Kernel\Factory;

class Form
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $module_id    = NULL ;
    protected $element_id   = NULL ;
    protected $_lib_js      = [];
    protected $_lib_css     = [];
    protected $_cdn_js      = [];
    protected $_cdn_css     = [];

    /* ************************************************** */
    /* ******************   TOOLS    ******************** */
    /* ************************************************** */

    private function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    private function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setModuleId( $id )
    {
        $this->module_id = $id ;
    }

    public function setElementId( $id )
    {
        $this->element_id = $id ;
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
        if ( is_string( $var ) && ! array_key_exists( md5( $var ) , $this->_lib_css ) )
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

    private function setCdnCSS( $var )
    {
        if ( is_string( $var ) )
        {
            $this->_cdn_css[ md5( $var ) ] = $var ;
        }
        else if ( is_array( $var ) )
        {
            foreach( $var as $row )
            {
                $this->_cdn_css[ md5( $row ) ] = $row ;
            }
        }
    }

    private function setCdnJS( $var )
    {
        if ( is_string( $var ) )
        {
            $this->_cdn_js[ md5( $var ) ] = $var ;
        }
        else if ( is_array( $var ) )
        {
            foreach( $var as $row )
            {
                $this->_cdn_js[ md5( $row ) ] = $row ;
            }
        }
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getModuleId()
    {
        return $this->module_id ;
    }

    public function getElementId()
    {
        return $this->element_id ;
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
                if ( !empty( $row ) ) $html.= '<script type="text/javascript" src="' . $this->site( $row ) . ( DEBUG ? '?' . time() : '' ) . '"></script>' . "\n" ;
            }
        }
        return $html ;
    }

    public function getCdnCSS()
    {
        $html = '' ;
        if ( !empty( $this->_cdn_css ) )
        {
            foreach( $this->_cdn_css as $row )
            {
                if ( !empty( $row ) ) $html.= '<link rel="stylesheet" href="' . $row . '" />' . "\n" ;
            }
        }
        return $html ;
    }

    public function getCdnJS()
    {
        $html = '' ;
        if ( !empty( $this->_cdn_js ) )
        {
            foreach( $this->_cdn_js as $row )
            {
                if ( !empty( $row ) ) $html.= '<script src="' . $row . '"></script>' . "\n" ;
            }
        }
        return $html ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

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
            $obj->setModuleId( $this->getModuleId() );
            $obj->setElementId( $this->getElementId() );

            $html = $obj->html( $field , $name , $value );

            $this->setLibCSS( $obj->getLibCSS() );
            $this->setLibJS( $obj->getLibJS() );

            $this->setCdnCSS( $obj->getCdnCSS() );
            $this->setCdnJS( $obj->getCdnJS() );

            return $html ;
        }
        else
        {
            throw new \App\Kernel\Exception("No PHP class for field type: " . $field->getType() . ' (field: ' . $field->getName() . ' - ' . FORM_PATH . '/' . $className . '.php) ');
        }
    }

    public function initLib()
    {
        $this->_lib_js  = [] ;
        $this->_lib_css = [] ;

        $this->_cdn_js  = [] ;
        $this->_cdn_css = [] ;
    }

    public function site( $url )
    {
        return $this->getApp()->request()->getUrl() . '/assets/vendor/' . ltrim($url, '/');
    }
}