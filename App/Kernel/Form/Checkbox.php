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

        $select = '' ;
        foreach( $field->getData('option') as $key => $opt )
        {
            //$select .= '<option value="' . $key . '"' . ( in_array( $key , $value ) ? " selected" : '' ) . '>' . $opt . '</option>' . "\n" ;
            if (count($opt->subpages) > 0 )
            {
                $select .= '<option class="label-info" value="' . $opt->id . '"' . ( in_array( $opt->id , $value ) ? " selected" : '' ) . '>1er - '. $opt->titre . '</option>' . "\n" ;
                foreach ($opt->subpages as $subpage => $opt)
                {
                    $select .= '<option class="warning" value="' . $opt->id . '"' . ( in_array( $opt->id , $value ) ? " selected" : '' ) . '>2e - '. $opt->titre . '</option>' . "\n" ;
                    if (count($opt->subpages) > 0 )
                    {
                        foreach ($opt->subpages as $subpage => $opt)
                        {
                            $select .= '<option class="success" value="' . $opt->id . '"' . ( in_array( $opt->id , $value ) ? " selected": '' ) . '>3e - '. $opt->titre . '</option>' . "\n" ;
                            if (count($opt->subpages) > 0 )
                            {
                                foreach ($opt->subpages as $subpage => $opt) // Catégorie la plus basse
                                {
                                    $select .= '<option class="danger" value="' . $opt->id . '"' . ( in_array( $opt->id , $value ) ? " selected" : '' ) . '>4e - '. $opt->titre . '</option>' . "\n" ;
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                $select .= '<option value="' . $key . '"' . ( in_array( $key , $value ) ? " selected" : '' ) . '>' . $opt . '</option>' . "\n" ;
            }
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

}