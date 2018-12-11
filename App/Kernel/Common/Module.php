<?php

namespace App\Kernel\Common;

class Module
{
    public function getId( $name )
    {
        $module = \DB::for_table('module')
            ->select('module_id')
            ->where(array('module_class_name' => $name , 'module_active' => 1))
            ->find_one();

        if ( $module )
        {
            return $module->module_id ;
        }

        return false ;
    }
}