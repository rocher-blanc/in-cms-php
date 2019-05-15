<?php

namespace App\Kernel\Back;

class Document extends \App\Kernel\Common\Document
{
    /* ************************************************** */
    /* ******************   TOOLS    ******************** */
    /* ************************************************** */

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    protected function post( $key )
	{
		return $this->getApp()->request->post( $key );
	}

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function getAll()
    {
        $rst = \DB::for_table('document')
            ->where_equal( 'document_module_id' , $this->getModuleId() )
            ->find_many();

        $arrayDoc = [] ;
        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $std = new \stdClass;
                $std->document_delete 	= true;
                $std->document_name 	= $row->document_name;
                $std->document_id 	 	= $row->document_id;
                $std->document_type 	= $row->document_type;
				$std->document_icon 	= $this->getIcon( $row->document_name );

                if ( $row->document_size > 1000 )
                {
                    // KO
                    $std->document_size = number_format($row->document_size/1000, 2, '.', ' ') . " Ko";
                }
                else if ( $row->document_size > 1000000 )
                {
                    // MO
                    $std->document_size = number_format($row->document_size/1000000, 2, '.', ' ') . " Mo";
                }
                else if ( $row->document_size > 1000000000 )
                {
                    // GO
                    $std->document_size = number_format($row->document_size/1000000000, 2, '.', ' ') . " Go";
                }
                else
                {
                    // O
                    $std->document_size = number_format($row->document_size, 2, '.', ' ') . " octets";
                }

                $arrayDoc[ $std->document_id ] = $std ;
            }
        }

        return $arrayDoc ;
    }

    public function delete()
    {
        $this->getNameById() ;

        $path = DOCUMENT_PATH . '/' . $this->getFolder() . '/' ;
        $doc  = $path . '/' . $this->getDocumentName() ;

        if ( file_exists( $doc ) )
        {
            unlink( $doc ) ;

            $document = \DB::for_table('document')
                ->where_id_is( $this->getDocumentId() )
                ->find_one();

            $document->delete();

            return true ;
        }

        return false ;
    }

    public function updateAlt()
    {
        $rst = \DB::for_table('document')
            ->where_id_is( $this->getDocumentId() )
            ->find_one();

        if ( $rst )
        {
            $rst->document_title = $this->getAltText();
            $rst->save();

            return true ;
        }
        else
        {
            return false ;
        }
    }

    public function upload()
    {
        $ct  = count( $_FILES[ $this->post('field') ]["name"] );
        $tab = [];

        for( $i = 0; $i <= $ct; $i ++ )
        {
            $name       = basename($_FILES[ $this->post('field') ]["name"][$i]);
            $ext        = explode( '.' , $name );
            $extension  = end( $ext );
            $name       = basename( $name , '.' . $extension );
            $name       = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;

            $rst = move_uploaded_file( $_FILES[ $this->post('field') ]["tmp_name"][$i] , UPLOAD_PATH . '/' . $name );

            if ( $rst !== false )
            {
                $document = \DB::for_table('document')->create() ;
                $document->document_name 		= $name;
                $document->document_size 		= $_FILES[ $this->post('field') ]["size"][$i];
                $document->document_type 		= $_FILES[ $this->post('field') ]["type"][$i];
                $document->document_module_id 	= $this->getModuleId();
                $document->save() ;

                $std = new \stdClass;
                $std->id = $document->document_id;
                $std->field = $this->post('field');

                $fieldEntityName = $std->field ;
                $entity = \App\Kernel\Container::getInstance()->module( $this->getModuleName() )->getEntity();
                $this->setDocumentId( $std->id ) ;
                $this->getNameById() ;
                $source = $this->rename();

                $std->name = $this->getDocumentName();
                $std->ico  = $this->getIcon( $this->getDocumentName() );
                $std->url  = $this->Factory()->Url()->get('module/' . $entity->getClassName() . '/deletedocument/id/' . $std->id );

                $tab[] = $std ;
            }
        }

        return $tab ;
    }

    public function download( string $name , string $url )
    {
        $content = file_get_contents( $url ) ;

        if ( $content !== false )
        {
            $extension  = end( explode( '.' , $name ) );
            $name       = basename( $name , '.' . $extension );
            $name       = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;
            $filepath   = UPLOAD_PATH . '/' . $name ;

            $rst = file_put_contents( $filepath , $content );

            if ( $rst !== false )
            {
                $document = \DB::for_table('document')->create() ;
                $document->document_name 		= $name;
                $document->document_size 		= filesize( $filepath );
                $document->document_type 		= mime_content_type( $filepath );
                $document->document_module_id 	= $this->getModuleId();
                $document->save() ;

                $this->setDocumentId( $document->document_id ) ;
                $this->getNameById() ;
                $this->rename();

                return $document->document_id ;
            }
            else
            {
                return false ;
            }
        }
        else
        {
            return false ;
        }
    }
}