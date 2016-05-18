<?php

namespace App\Kernel\Form;

class Link
{
	public function html( $field, $name, $value = NULL )
	{
        $html = '' ;
        $input = '<input type="text" value="' . $value . '" placeholder="http://" class="form-control" name="' . $name . '" id="id_' . $name . '" maxlength="' . $field->getData('maxLength') . '"' . ( $field->isRequired() ? ' required="1"' : '' ) . ' />' ;

        $html = '
        <div class="input-group input-group-icon">
            <span class="input-group-addon">
                <span class="icon">
                    <i class="fa fa-link"></i>
                </span>
            </span>
            ' . $input . '
        </div>' ;

        return $html ;
	}
}