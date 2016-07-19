<?php

namespace App\Kernel\Back;

class Document extends \App\Kernel\Document
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $module_id = NULL ;

    public function setModuleId( $var )
    {
        $this->module_id = $var ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getModuleId()
    {
        return $this->module_id ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
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

    private function updateName()
    {
        $exp 	= explode( "." , $this->getDocumentName() ) ;
        $ext 	= end( $exp ) ;
        $extlen = ( strlen( $ext ) + 1 ) * -1 ;

        $name = substr( $this->getDocumentName() , 0 , $extlen ) ;
        $name = $this->Factory()->Url()->encode( $this->getDocumentId() . "-" . $name ) . "." . $ext ;

        return $name ;
    }

    public function rename()
    {
        $path = DOCUMENT_PATH . '/' . $this->getFolder() . '/' ;
        $doc  = UPLOAD_PATH . '/' . $this->getDocumentName() ;

        if ( file_exists( $doc ) )
        {
            $name = $this->updateName();
            rename( $doc , $path . $name ) ;

            $my = \DB::for_table('document')
                ->where_id_is( $this->getDocumentId() )
                ->find_one();

            $my->document_name = $name;
            $my->save() ;

            $this->setDocumentName( $name ) ;
        }
    }

    public function upload( $path )
    {
        $upload_dir 	= $path . '/' ;
        $upload_url 	= str_replace( WEB_PATH , '' , $upload_dir ) ;
        $upload_handler = new \App\Kernel\Back\DocumentUpload([
            'module_id' => $this->getModuleId(),
            'upload_dir' => $upload_dir,
            'upload_url' => $this->Factory()->Url()->get( $upload_url , true ),
            'param_name' => 'files'
        ]);
    }
}