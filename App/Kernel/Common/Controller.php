<?php

namespace App\Kernel\Common;

class Controller
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_url_module = "" ;
    protected $_main = false;

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setModuleUrl( $var )
    {
        $this->_url_module = $var ;
    }

    public function setMain()
    {
        $this->_main = true ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getModuleUrl()
    {
        return $this->_url_module . '/' ;
    }

    /* ************************************************** */
    /* ******************    ISER    ******************** */
    /* ************************************************** */

    protected function isMain()
    {
        return $this->_main ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    protected function loadModuleUrl()
    {
        if ( ! $this->isMain() )
        {
            $row = \DB::for_table('module_lang')
                ->select('module_lang_url')
                ->where(['module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang_module_id' => $this->getEntityId()])
                ->find_one();

            $this->setModuleUrl( $row->module_lang_url ) ;
        }
    }

    protected function renderForm( $values )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        return $View->fetch( 'module/form.twig' , $values );
    }
}