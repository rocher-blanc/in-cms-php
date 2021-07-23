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
        $this->_lib_js  = 'cmsmedias/canvas/js/components/select-boxes.js';
		$this->_lib_css = 'cmsmedias/canvas/css/src/components/select-boxes.css';
		
		$select = '' ;
		if ( ! $field->isRequired() )
		{
			$select .= '<option value=""' . ( '' == $value ? ' selected' : '' ) . ' style="font-style:italic;">----- Aucune sélection -----</option>' ;
		}

        if ( $field->isParent() )
        {
            if ( ! $field->getData('noEmptyValue') ) $select = '<option value=""' . ( $value === NULL ? ' selected' : '' ) . '>---</option>' ;
//            $select.= $this->chieldParent( $field->getData('option') , $field->getData('target') , $value , "" ) ;
            $select.= $this->chieldParent( $field->getData('option') , "titre" , $value , "", $field->getData('limit') ) ;
        }
        else
        {
//        	dump( $field );
			if ( !empty( $field->getData('option') ) )
			{
				foreach( $field->getData('option') as $key => $opt )
				{
					if( is_array( $opt) )
					{
						$select .= '<optgroup label="'.$key.'">';
						foreach( $opt as $keyOpt => $optOpt )
						{
							$select .= '<option value="' . $keyOpt . '"' . ( $keyOpt == $value ? " selected" : '' ) . '>' . $optOpt . '</option>' . "\n" ;
						}
						$select .= '</optgroup>';
					}
					else
					{
						$select .= '<option value="' . $key . '"' . ( $key == $value ? ' selected' : '' ) . '>' . $opt . '</option>' ;
					}
				}
			}
        }

		return '
		<select name="' . $name . '" id="id_' . $field->getColumn() . '" class="form-control" data-plugin-selectTwo>
			' . $select . '
		</select>' ;
	}

    private function chieldParent( $chield , $target , $value , $hierarchy, $limit = null )
    {
        $select = '';
        $index  = 1;
        foreach( $chield as $row )
        {
            if ( $row->noview != true )
            {
                if($row->level < $limit or $limit === null)
                {
                    $select .= '<option value="' . $row->id . '"' . ( $row->id == $value ? ' selected' : '' ) . '>' ;
                    for( $i = 0; $i < $row->level; $i++ )
                    {
                        $select .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
                    }
                    $select .= '<span style="font-weight: bold;">' . $hierarchy . $index .'.</span> ' ;
                    $select .= $row->$target . '</option>' ;

                    if ( !empty( $row->subpages ) )
                    {
                        $select .= $this->chieldParent( $row->subpages , $target , $value , $hierarchy . $index . ".", $limit ) ;
                    }
                }
            }
            $index++;
        }

        return $select ;
    }
}