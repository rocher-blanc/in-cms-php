<?php

namespace App\Kernel\Front;

class Media extends \App\Kernel\Common\Media
{
    public function upload( $fieldName , $name )
    {
        $upload_dir 	= UPLOAD_PATH . '/' ;
        $upload_url 	= str_replace( WEB_PATH , '' , $upload_dir ) ;
        $img = new Image([
            'module_id'   => $this->getModuleId(),
            'upload_dir'  => $upload_dir,
            'field'       => $name,
            'upload_url'  => $this->Factory()->Url()->get( $upload_url , true ),
            'param_name'  => $fieldName,
        ]);

        $rst = $img->upload() ;

        if ( $rst !== false )
        {
            $entity = \App\Kernel\Container::getInstance()->module( $this->getModuleName() )->getEntity();
            $field  = $entity->build( $name )->field();
            $this->setImageId( $rst->id ) ;
            $this->getNameById() ;
            $source = $this->rename();

            $rst->url     = $this->Factory()->Url()->get('module/' . $entity->getClassName() . '/deletemedia/' . $rst->id );
            $rst->caption = $source;
            $rst->mini    = $this->Factory()->Url()->get( $entity->getPathImage( false ) . '/t/' . $rst->caption , true );

//            $this->genImages( $field );
        }

        return $rst ;
    }
}