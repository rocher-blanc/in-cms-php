<?php

namespace App\Kernel\Form;

class Select extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		$this->initLib() ;
		return $this->getSelect( $field, $name, $value ) ;
	}
	
	private function getSelect( $field, $name, $value = NULL )
	{
        $this->_lib_js  = 'select2/dist/js/select2.min.js';
		$this->_lib_css = 'select2/dist/css/select2.min.css';
		
		$select = '' ;
		if ( ! $field->isRequired() )
		{
			$select .= '<option value=""' . ( '' == $value ? ' selected' : '' ) . ' style="font-style:italic;">----- Aucune sélection -----</option>' ;
		}

        if ( $field->isParent() )
        {
            if ( ! $field->getData('noEmptyValue') ) $select = '<option value=""' . ( $value === NULL ? ' selected' : '' ) . '>---</option>' ;
            $select.= $this->chieldParent( $field->getData('option') , $field->getData('target') , $value , "" ) ;
        }
        else
        {
            foreach( $field->getData('option') as $key => $opt )
            {
                $select .= '<option value="' . $key . '"' . ( $key == $value ? ' selected' : '' ) . '>' . $opt . '</option>' ;
            }
        }
		
		return '
		<select name="' . $name . '" id="id_' . $field->getColumn() . '" data-plugin-selectTwo class="form-control">
			' . $select . '
		</select>' ;
	}

    private function chieldParent( $chield , $target , $value , $hierarchy )
    {
        $index = 1;
        foreach( $chield as $row )
        {
            if ( $row->noview != true )
            {
                $select .= '<option value="' . $row->id . '"' . ( $row->id == $value ? ' selected' : '' ) . '>' ;
                for( $i = 0; $i < $row->level; $i++)
                {
                    $select .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
                }
                $select .= '<span style="font-weight: bold;">' . $hierarchy . $index .'.</span> ' ;
                $select .= $row->$target . '</option>' ;
                if ( !empty( $row->subpages ) )
                {
                    $select .= $this->chieldParent( $row->subpages , $target , $value , $hierarchy . $index . "." ) ;
                }
            }
            $index++;
        }

        return $select ;
    }
}