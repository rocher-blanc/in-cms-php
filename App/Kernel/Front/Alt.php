<?php

namespace App\Kernel\Front;

class Alt extends \App\Kernel\Common\Alt
{
    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function getOne()
    {
        $rst = \DB::for_table('media_alt')
            ->select('media_alt_value')
            ->where_equal('media_alt_module_id' , $this->getModuleId() )
            ->where_equal('media_alt_element_id' , $this->getElementId() )
            ->where_equal('media_alt_field_name' , $this->getFieldName() )
            ->where_equal('media_alt_lang_id' , $this->getLangId() )
            ->find_one();

        if ( $rst )
        {
            $this->setValue( $rst->media_alt_value );
        }
    }
}