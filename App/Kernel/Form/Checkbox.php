<?php

namespace App\Kernel\Form;

use App\Kernel\Back\Media;

class Checkbox extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{
		$this->initLib() ;

		if ( $field->isParent() == true )       return $this->getMultiselectParent( $field , $name , $value ) ;
		else                                    return $this->getMultiselect( $field , $name , $value ) ;
	}

    private function getMultiselect( $field, $name, $value = NULL )
    {
        switch ( $field->getMode() )
        {
            case 'bigList' :
                return $this->View()->fetch( 'form/checkbox_biglist.twig' , [
                    'name' => $name,
                    'value' => $value,
                    'option' => $field->getData('option'),
                    'required' => $field->isRequired()
                ]);
            break;

            case 'simple' :
                return $this->View()->fetch( 'form/checkbox_simple.twig' , [
                    'name' => $name,
                    'value' => $value,
                    'option' => $field->getData('option'),
                    'required' => $field->isRequired()
                ]);
            break;

            case 'checkableTiles' :
                return $this->checkableTiles( $field, $name, $value );
            break;

            default:
                return $this->classic( $field, $name, $value );
            break;
        }
    }

    private function checkableTiles( $field, $name, $values = NULL )
    {
        $arrayOption = [];
        $Entity      = \App\Kernel\Container::getInstance()->module( $field->getData('object') )->getEntity() ;

        if ( $Entity->hasImage() )
        {
            $opt = $field->getData('option');

            if ( $opt )
            {
                $Repo = \App\Kernel\Container::getInstance()->module( $field->getData('object') )->getRepository(true) ;

                foreach( $opt as $id => $value )
                {
                    $imageId = $Repo->getImage( $id );

                    if ( $imageId )
                    {
                        $imageId = $imageId->get( $Entity->get( $Entity->getFirstImageName() )->getColumn() );

                        $media = new Media;
                        $media->setImageId( $imageId );
                        $media->getNameById();
                        $image = $Entity->getFolder() . '/' . $media->getMini( $media->getImageName() , 't' , 100 , 100 ) ;

                        $arrayOption[ $id ] = [
                            'value' => $value,
                            'img'   => $this->Factory()->Url()->image( $image , true )
                        ];
                    }
                    else
                    {
                        $arrayOption[ $id ] = [
                            'value' => $value,
                            'img'   => ''
                        ];
                    }
                }
            }

            return $this->View()->fetch( 'form/checkbox_checkabletiles.twig' , [
                'name' => $name,
                'value' => $values,
                'option' => $arrayOption,
                'required' => $field->isRequired()
            ]);
        }
        else
        {
            return $this->classic( $field, $name, $values );
        }
    }

    private function classic( $field, $name, $value = NULL )
    {
        if ( $value === NULL ) $value = [];

        $select = '' ;
        foreach( $field->getData('option') as $key => $opt )
        {
            if ( is_array( $opt ) )
            {
                $select .= '<optgroup label="'.$key.'">';
                foreach( $opt as $keyOpt => $optOpt )
                {
                    $select .= '<option value="' . $keyOpt . '"' . ( in_array( $keyOpt , $value ) ? " selected" : '' ) . '>' . $optOpt . '</option>' . "\n" ;
                }
                $select .= '</optgroup>';
            }
            else
            {
                $select .= '<option value="' . $key . '"' . ( in_array( $key , $value ) ? " selected" : '' ) . '>' . $opt . '</option>' . "\n";
            }
        }

        return '
        <select name="' . $name . '[]" id="id_' . $field->getColumn() . '" class="form-control" data-plugin-selectPicker data-size="10" multiple title="Sélectionner les options">
            ' . $select . '
        </select>' ;
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