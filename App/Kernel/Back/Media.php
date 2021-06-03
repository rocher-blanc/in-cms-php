<?php

namespace App\Kernel\Back;

use App\Kernel\Common\ImageGenerator;

class Media extends \App\Kernel\Common\Media
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */

    public function getAllByGallery()
    {
        $rst = \DB::for_table('media')
            ->where_equal( 'media_gallery' , $this->getGalleryId() )
            ->find_many();

        $arrayMedia = [] ;
        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $std = new \stdClass;
                $std->media_delete 		= true;
                $std->media_name 		= $row->media_name;
                $std->media_id 	 		= $row->media_id;
                $std->media_mini_name   = $this->Factory()->Url()->image( $this->getFolder() . '/' . $this->getMiniName( $row->media_name ) , true ) ;

                $arrayMedia[ $std->media_id ] = $std ;
            }
        }

        return $arrayMedia ;
    }

    public function getAllModel()
    {
        $rst = \DB::for_table('media')
            ->where_equal( 'media_module_id' , $this->getModuleId() )
            ->where_equal( 'media_field' , $this->getField() )
            ->find_many();

        $arrayMedia = [] ;
        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $std = new \stdClass;
                $std->media_delete 		= true;
                $std->media_name 		= $row->media_name;
                $std->media_id 	 		= $row->media_id;
                $std->media_mini_name   = $this->Factory()->Url()->image( $this->getFolder() . '/' . $this->getMiniName( $row->media_name ) , true ) ;

                $arrayMedia[ $std->media_id ] = $std ;
            }
        }

        return $arrayMedia ;
    }

	public function getAll()
	{
		$rst = \DB::for_table('media')
			->where_equal( 'media_module_id' , $this->getModuleId() )
			->find_many();

		$arrayMedia = [] ;
		if ( $rst )
		{
			foreach( $rst as $row )
			{
				$std = new \stdClass;
				$std->media_delete 		= true;
				$std->media_name 		= $row->media_name;
				$std->media_id 	 		= $row->media_id;
				$std->media_mini_name   = $this->Factory()->Url()->image( $this->getFolder() . '/' . $this->getMiniName( $row->media_name ) , true ) ;

				$arrayMedia[ $std->media_id ] = $std ;
			}
		}

		return $arrayMedia ;
	}
	
	public function getIdByName()
	{
		$rst = \DB::for_table('media')
			->select('media_id')
			->where_equal( 'media_name' , $this->getImageName() )
			->find_one();
		
		if ( $rst ) $this->setImageId( $rst->media_id ) ;
		
		if ( $rst )	return true ;
		else		return false ;
	}
	
	public function exist()
	{
		$ct = \DB::for_table('media')
			->where_equal( 'media_id' , $this->getImageId() )
			->count();
		
		if ( $ct != 0 )	return true ;
		else			return false ;
	}

	protected function create( $nameFile , $content )
	{
		$fp = fopen( $nameFile , 'w+' ) ;
		$rst = fwrite( $fp , $content ) ;
		fclose( $fp ) ;

		return $rst ;
	}

	public function createByUrl( $uri , $fieldName )
	{
		if ( @file_get_contents( $uri ) !== false )
		{
			$content  = file_get_contents( $uri );
			$size     = getimagesize( $uri );
			$fileName = end( explode( '/' , $uri ) );
			$this->create( UPLOAD_PATH . "/" . $fileName , $content ) ;

			$media = \DB::for_table('media')->create() ;
			$media->media_name 		= $fileName;
			$media->media_size 		= 0;
			$media->media_type 		= $size['mime'];
			$media->media_module_id = $this->getModuleId();
			$media->save() ;

			$entity = \App\Kernel\Container::getInstance()->module( $this->getModuleName() )->getEntity();
			$field = $entity->build( $fieldName )->field();
			$this->setImageId( $media->media_id ) ;
			$this->getNameById() ;
			$source = $this->rename();

			if ( $field->hasThumb() )
			{
				foreach( $field->getThumb() as $thumb )
				{
					// width, height
					$this->genThumb( $thumb[0] , $thumb[1] ) ;
				}
			}

			return $media->media_id;
		}
		else
		{
			return NULL;
		}

	}

	public function uploadLib( $path )
    {
        $this->setFolder('_lib');

        $upload_dir 	= $path . '/' ;
        $upload_url 	= str_replace( WEB_PATH , '' , $upload_dir ) ;

        $img = new Image([
            'module_id'   => NULL,
            'gallery'     => $this->getGalleryId(),
            'upload_dir'  => $upload_dir,
            'field'       => NULL,
            'param_name'  => 'file',
        ]);

        $rst = $img->upload() ;

        if ( $rst !== false )
        {
            $this->setImageId( $rst->id ) ;
            $this->getNameById() ;
            $source = $this->rename();

            return true ;
        }

        return false ;
    }

    public function upload( $path )
    {
        foreach( $_FILES as $key => $value )
        {
            $fieldName = $key ;
        }

        $upload_dir 	= $path . '/' ;
        $upload_url 	= str_replace( WEB_PATH , '' , $upload_dir ) ;
        $img = new Image([
            'module_id'   => $this->getModuleId(),
            'upload_dir'  => $upload_dir,
            'gallery'     => NULL,
            'field'       => $this->getApp()->request->post('model') == 1 ? $this->getApp()->request->post('field') : NULL,
            'upload_url'  => $this->Factory()->Url()->get( $upload_url , true ),
            'param_name'  => $fieldName,
        ]);

        $rst = $img->upload() ;

        if ( $rst !== false )
        {
            $fieldEntityName = $this->getApp()->request->post('field') ;
            $entity = \App\Kernel\Container::getInstance()->module( $this->getModuleName() )->getEntity();
            $field = $entity->build( $fieldEntityName )->field();
            $this->setImageId( $rst->id ) ;
            $this->getNameById() ;
            $source = $this->rename();

            $rst->url  = $this->Factory()->Url()->get('module/' . $entity->getClassName() . '/deletemedia/' . $rst->id );
            $rst->caption = $source;
            $rst->mini = $this->Factory()->Url()->get( $entity->getPathImage( false ) . '/t/' . $rst->caption , true );

            $generator = new ImageGenerator( $this->getFolder(), $this->getImageName() );
			$generator->genImages( $field );
//            $this->genImages($field);
        }

        return $rst ;
    }

    public function editor()
    {
        $folder     = IMAGE_PATH . "/" . $this->getFolder() . "/e" ;
        $name       = basename($_FILES['filewysiwyg']["name"]);
        $ext        = explode( '.' , $name );
        $extension  = end( $ext );
        $name       = basename( $name , '.' . $extension );
        $name       = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;

        if ( ! file_exists( $folder . "/" . $name ) )
        {
            $exist = true ;
            $i     = 1 ;
            while( $exist == true )
            {
                $name = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '-' . $i . '.' . $extension ;
                if ( file_exists( $folder . "/" . $name ) )
                {
                    $i++;
                }
                else
                {
                    $exist = false ;
                }
            }
        }

        $rst = move_uploaded_file( $_FILES['filewysiwyg']["tmp_name"] , $folder . "/" . $name );

        if ( $rst ) return str_replace( WEB_PATH , '' , $folder . "/" . $name );
        else        return false ;
    }
	
	public function delete()
	{
		$this->getNameById() ;
		
		$path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
		$img  = $path . '/' . $this->getImageName() ;
		
		if ( file_exists( $img ) )
		{
			unlink( $img ) ;
			
			$ext = $this->getExtension( $this->getImageName() ) ;
			$name = substr( $this->getImageName() , 0 , ( strlen( $ext ) * -1 ) ) ;
			
			$typeArray = ["c","t"];
			foreach( $typeArray as $type )
			{
				$tab = glob( $path . $type . '/' . $name . '-*' . $ext ) ;
				if ( $tab )
				{
					foreach( $tab as $img )
					{
						if ( file_exists( $img ) ) unlink( $img ) ;
					}
				}
			}
			
			$media = \DB::for_table('media')
				->where_id_is( $this->getImageId() )
				->find_one();
			
			$media->delete();
			
			return true ;
		}
		
		return false ;
	}


	/*----------------------------------------------------------------------*/
	/*----------                                                  ----------*/
	/*----------                       TOOLS                      ----------*/
	/*----------                                                  ----------*/
	/*----------------------------------------------------------------------*/

	private function getMiniName( $name )
	{
		return $this->getMini( $name , 't' , 100 , 100 ) ;
	}
}