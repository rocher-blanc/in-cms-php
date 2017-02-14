<?php

namespace App\Kernel\Form;

class Text extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
        $html = '' ;
        $input = '<input type="text" value="' . htmlspecialchars( $value ) . '" class="form-control" name="' . $name . '" id="id_' . $name . '" maxlength="' . $field->getData('maxLength') . '"' . ( $field->isRequired() ? ' required="1"' : '' ) . ' />' ;

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