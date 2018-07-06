<?php

namespace App\Kernel\Back;

class Document extends \App\Kernel\Common\Document
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    private $module_id   = NULL ;
	private $module_name = NULL ;

	/* ************************************************** */
	/* ******************   SETTER   ******************** */
	/* ************************************************** */

	public function setModuleId( $var )
    {
        $this->module_id = $var ;
    }

	public function setModuleName( $var )
	{
		$this->module_name = $var ;
	}

	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */

    public function getModuleId()
    {
        return $this->module_id ;
    }

    public function getModuleName()
    {
        return $this->module_name ;
    }

	/* ************************************************** */
	/* ******************   TOOLS    ******************** */
	/* ************************************************** */

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

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

    private function updateName()
    {
        $exp 	= explode( "." , $this->getDocumentName() ) ;
        $ext 	= end( $exp ) ;
        $extlen = ( strlen( $ext ) + 1 ) * -1 ;

        $name = substr( $this->getDocumentName() , 0 , $extlen ) ;
        $name = $this->Factory()->Url()->encode( $this->getDocumentId() . "-" . $name ) . "." . $ext ;

        return $name ;
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
    	$ct  = count( $_FILES[ $this->post('field') ]["name"] );
    	$tab = [];

    	for( $i = 0; $i <= $ct; $i ++ )
		{
			$name       = basename($_FILES[ $this->post('field') ]["name"][$i]);
			$ext        = explode( '.' , $name );
			$extension  = end( $ext );
			$name       = basename( $name , '.' . $extension );
			$name       = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;

			$rst = move_uploaded_file( $_FILES[ $this->post('field') ]["tmp_name"][$i] , $path . '/' . $name );

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
}