<?php

namespace App\Kernel\Back;

class Form
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_lib_js    ;
    protected $_lib_css   ;
    protected $_cdn_js    ;
    protected $_cdn_css   ;
    protected $_id        = NULL ;
    protected $view       = NULL ;
    protected $module_id  = NULL ;
    protected $element_id = NULL ;
    protected $folder     = NULL ;

    /* ************************************************** */
    /* ******************   TOOLS   ********************* */
    /* ************************************************** */

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

    protected function View()
    {
        if ( $this->view === NULL )
        {
            $this->view = $this->Container()->newClass('App\Kernel\View') ;
        }

        return $this->view ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setId( $var )
    {
        $this->_id = $var;
    }

    public function setModuleId( $id )
    {
        $this->module_id = $id ;
    }

    public function setElementId( $id )
    {
        $this->element_id = $id ;
    }

    /**
     * @param null $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    /**
     * @return null
     */
    public function getFolder()
    {
        return $this->folder;
    }

    public function getModuleId()
    {
        return $this->module_id ;
    }

    public function getElementId()
    {
        return $this->element_id ;
    }

    public function getLibCss()
    {
        return $this->_lib_css ;
    }

    public function getLibJs()
    {
        return $this->_lib_js ;
    }

    public function getCdnCss()
    {
        return $this->_cdn_css ;
    }

    public function getCdnJs()
    {
        return $this->_cdn_js ;
    }

    public function getId()
    {
        return $this->_id ;
    }

    /* ************************************************** */
    /* *****************  FUNCTIONS  ******************** */
    /* ************************************************** */

    public function initLib()
    {
        $this->_lib_js  = '' ;
        $this->_lib_css = '' ;
    }
}