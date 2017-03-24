<?php

namespace App\Kernel\Form;

class Textarea extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
    {
        $html = '' ;
        $input = '<textarea class="form-control' . ( $field->getData('editor') == true ? ' cke' : '' ) . '" name="' . $name . '" id="id_' . $field->getColumn() . '"' . ( $field->isRequired() ? ' required="1"' : '' ) . '>' . htmlspecialchars( $value ) . '</textarea>' ;

        if ( $field->getData('editor') == true )
        {
            $this->_lib_js  = [
                'cmsmedias/js/ckeditor/ckeditor.js',
                'cmsmedias/js/ckeditor.js'
            ];
        }

        if ( $field->hasLang() )
        {
            $html = '
            <div style="margin-bottom:10px;" class="input-group input-group-icon">
                <span class="input-group-addon">
                    <span class="icon">
                        <i class="flag-icon flag-icon-' . $field->getData('flag') . '"></i>
                    </span>
                </span>
                ' . $input . '
            </div>' ;
        }
        else
        {
            $html = $input;
        }

        return $html ;
    }
}