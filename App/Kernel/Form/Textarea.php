<?php

namespace App\Kernel\Form;

class Textarea
{
	public function html( $field, $name, $value = NULL )
    {
        $html = '' ;
        $input = '<textarea class="form-control' . ( $field->getData('editor') == true ? ' cke' : '' ) . '" name="' . $name . '" id="id_' . $field->getColumn() . '"' . ( $field->isRequired() ? ' required="1"' : '' ) . '>' . $value . '</textarea>' ;

        if ( $field->getData('editor') == true )
        {
            $this->_lib_js  = [
                'ckeditor/ckeditor.js',
                'ckeditor/adapters/jquery.js'
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

    public function getLibJs()
    {
        return $this->_lib_js ;
    }
}