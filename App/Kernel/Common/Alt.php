<?php

namespace App\Kernel\Common;

class Alt
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $module_id  = NULL ;
    protected $element_id = NULL ;
    protected $lang_id    = NULL ;
    protected $field_name = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setModuleId( $var )
    {
        $this->module_id = $var ;
    }

    public function setElementId( $var )
    {
        $this->element_id = $var ;
    }

    public function setLangId( $var )
    {
        $this->lang_id = $var ;
    }

    public function setFieldName( $var )
    {
        $this->field_name = $var ;
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

    public function getLangId()
    {
        return $this->lang_id ;
    }

    public function getFieldName()
    {
        return $this->field_name ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */


}