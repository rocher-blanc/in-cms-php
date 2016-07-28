<?php

namespace App\Kernel\Back;

class Form
{
    public function initLib()
    {
        $this->_lib_js  = '' ;
        $this->_lib_css = '' ;
    }

    public function getLibCss()
    {
        return $this->_lib_css ;
    }

    public function getLibJs()
    {
        return $this->_lib_js ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }
}