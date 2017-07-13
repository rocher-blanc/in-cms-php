<?php

namespace App\Kernel\Back;

class Alt extends \App\Kernel\Common\Alt
{
	/* ************************************************** */
	/* ****************   FUNCTIONS   ******************* */
	/* ************************************************** */
	
	public function getOne()
    {
        $tab = [];

        $rst = \DB::for_table('media_alt')
            ->where_equal('media_alt_module_id' , $this->getModuleId() )
            ->where_equal('media_alt_element_id' , $this->getElementId() )
            ->where_equal('media_alt_field_name' , $this->getFieldName() )
            ->find_many();

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab[ $row->media_alt_lang_id ] = $row->media_alt_value ;
            }
        }

        return $tab ;
    }

    public function update()
    {
        $rst = \DB::for_table('media_alt')
            ->where_equal('media_alt_module_id' , $this->getModuleId() )
            ->where_equal('media_alt_element_id' , $this->getElementId() )
            ->where_equal('media_alt_field_name' , $this->getFieldName() )
            ->where_equal('media_alt_lang_id' , $this->getLangId() )
            ->find_one();

        if ( ! $rst )
        {
            $rst = \DB::for_table('media_alt')->create();
            $rst->set('media_alt_module_id' , $this->getModuleId() );
            $rst->set('media_alt_element_id' , $this->getElementId() );
            $rst->set('media_alt_field_name' , $this->getFieldName() );
            $rst->set('media_alt_lang_id' , $this->getLangId() );
        }
        $rst->set('media_alt_value' , $this->getValue() );
        $rst->save();
    }

    public function delete()
    {
        $rst = \DB::for_table('media_alt')
            ->where_equal('media_alt_module_id' , $this->getModuleId() )
            ->where_equal('media_alt_element_id' , $this->getElementId() )
            ->delete_many();
    }
}