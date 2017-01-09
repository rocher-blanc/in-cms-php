<?php

namespace App\Kernel\Form;

class Builder extends \App\Kernel\Back\Form
{
    public function html( $field, $name, $value = NULL )
    {
        $this->value = $value ;

        $this->_lib_js  = [
            'cmsmedias/libs/lodash.min.js',
            'gridstack/dist/gridstack.min.js',
            'gridstack/dist/gridstack.jQueryUI.js',
            'cmsmedias/js/jbuilder.js'
        ];

        $this->_lib_css = [
            'cmsmedias/libs/materialize.jcontent.min.css',
            'cmsmedias/css/jbuilder.css'
        ];

        $this->_cdn_css = [
            'http://fonts.googleapis.com/icon?family=Material+Icons'
        ];

        $html = '
<div class="jbuilder">
    <div class="builder">
        <div class="actions">
            <a class="btn-floating waves-effect waves-light blue addItem" data-grid="grid-stack"><i class="material-icons">add</i></a>
        </div>
        <div class="grid-stack"></div>
    </div>
</div>' ;

        return $html;
    }
}