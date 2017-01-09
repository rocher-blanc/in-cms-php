<?php

namespace App\Kernel\Back;

class Form
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_lib_js  = '' ;
    protected $_lib_css = '' ;
    protected $_cdn_js  = '' ;
    protected $_cdn_css = '' ;
    protected $_id      = NULL ;

    /* ************************************************** */
    /* ******************   TOOLS   ********************* */
    /* ************************************************** */

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function setId( $var )
    {
        $this->_id = $var;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

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