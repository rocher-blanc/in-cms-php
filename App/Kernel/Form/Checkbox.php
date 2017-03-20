<?php

namespace App\Kernel\Form;

class Checkbox extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		$this->initLib() ;

		if ( $field->isParent() == true )   return $this->getMultiselectParent( $field , $name , $value ) ;
		else                                return $this->getMultiselect( $field , $name , $value ) ;
	}

    private function getMultiselect( $field, $name, $value = NULL )
    {
        $this->_lib_js  = 'bootstrap-multiselect/dist/js/bootstrap-multiselect.js';
        $this->_lib_css = 'bootstrap-multiselect/dist/css/bootstrap-multiselect.css';

        if ( $value === NULL ) $value = [];

        $select = '' ;
        foreach( $field->getData('option') as $key => $opt )
        {
            $select .= '<option value="' . $key . '"' . ( in_array( $key , $value ) ? " selected" : '' ) . '>' . $opt . '</option>' . "\n" ;
        }

        return '
        <div class="input-group btn-group">
            <span class="input-group-addon">
                    <i class="fa fa-th-list"></i>
            </span>
            <select name="' . $name . '" id="id_' . $field->getColumn() . '" data-plugin-multiselect multiple="multiple" data-live-search="true">
                ' . $select . '
            </select>
        </div>' ;
    }

    private function getMultiselectParent( $field, $name, $value = NULL )
    {
        if ( $value === NULL ) $value = [];

        return '
        <div class="checkbox-parent">
            <div class="input-group">
                ' . $this->chieldParent( $field->getData('option') , $field->getData('target') , $value , "" , $name ) . '
            </div>
        </div>' ;
    }

    private function chieldParent( $chield , $target , $value , $hierarchy , $name )
    {
        $checkbox = '';
        $index  = 1;
        foreach( $chield as $row )
        {
            if ( $row->noview != true )
            {
                for( $i = 0; $i < $row->level; $i++ )
                {
                    $checkbox .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
                }
                $checkbox .= '<input type="checkbox" name="' . $name . '[]" value="' . $row->id . '"' . ( in_array( $row->id , $value ) ? ' checked' : '' ) . ' />' ;
                $checkbox .= '&nbsp;<span style="font-weight: bold;">' . $hierarchy . $index .'.</span> ' ;
                $checkbox .= $row->$target . '<br />' ;

                if ( !empty( $row->subpages ) )
                {
                    $checkbox .= $this->chieldParent( $row->subpages , $target , $value , $hierarchy . $index . "." , $name ) ;
                }
            }
            $index++;
        }

        return $checkbox ;
    }

}