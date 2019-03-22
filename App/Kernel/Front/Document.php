<?php

namespace App\Kernel\Front;

class Document extends \App\Kernel\Common\Document
{
    public function upload( $field )
    {
        $name       = basename($_FILES[ $field ]["name"]);
        $ext        = explode( '.' , $name );
        $extension  = end( $ext );
        $name       = basename( $name , '.' . $extension );
        $name       = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;

        $rst = move_uploaded_file( $_FILES[ $field ]["tmp_name"] , UPLOAD_PATH . '/' . $name );

        if ( $rst !== false )
        {
            $document = \DB::for_table('document')->create() ;
            $document->document_name 		= $name;
            $document->document_size 		= $_FILES[ $field ]["size"];
            $document->document_type 		= $_FILES[ $field ]["type"];
            $document->document_module_id 	= $this->getModuleId();
            $document->save() ;

            $std = new \stdClass;
            $std->id = $document->document_id;
            $std->field = $field;

            $fieldEntityName = $std->field ;
            $entity = \App\Kernel\Container::getInstance()->module( $this->getModuleName() )->getEntity();
            $this->setDocumentId( $std->id ) ;
            $this->getNameById() ;
            $source = $this->rename();

            $std->name = $this->getDocumentName();
            $std->ico  = $this->getIcon( $this->getDocumentName() );
            $std->url  = $this->Factory()->Url()->get('module/' . $entity->getClassName() . '/deletedocument/id/' . $std->id );

            return true ;
        }

        return NULL ;
    }
}