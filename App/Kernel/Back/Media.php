<?php

namespace App\Kernel\Back;

class Media extends \App\Kernel\Common\Media
{
	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */
	
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

            if ( $field->hasThumb() )
            {
                foreach( $field->getThumb() as $thumb )
                {
                    // width, height
                    $this->genThumb( $thumb[0] , $thumb[1] ) ;
                }
            }
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
	
	private function getMiniName( $name )
	{
		return $this->getMini( $name , 't' , 100 , 100 ) ;
	}
	
	private function getExtension( $name )
	{
		$exp = explode( "." , $name ) ;
		return '.' . end( $exp ) ;
	}
	
	public function genThumb( $width , $height , $crop = false )
	{
		$path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
		$img  = $path . $this->getImageName() ;
		
		if ( $crop == true ) 	$subfolder = 'c' ;
		else					$subfolder = 't' ;
		
		try {
			$miniName = $this->updateName( $this->getImageName() , $width . "x" . $height ) ;
			$file = $path . $subfolder . "/" . $miniName ;
			
			if ( ! file_exists( $file ) && file_exists( $img ) )
			{
				$tmpImg = new \abeautifulsite\SimpleImage( $img );
                $tmpImg->best_fit( $width , $height );

                $destImg = new \abeautifulsite\SimpleImage(null, $width, $height, "#FFF");
                $destImg->overlay($tmpImg)->save($file);
			}
			
			return $miniName ;
		} catch(Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}
	
	public function genCropDefaut( $width , $height )
	{
		return $this->genThumb( $width , $height , true ) ;
	}
	
	public function crop()
	{
		$x  = $this->getApp()->request->post('crop_x') ;
		$y  = $this->getApp()->request->post('crop_y') ;
		$x2 = $this->getApp()->request->post('crop_x2') ;
		$y2 = $this->getApp()->request->post('crop_y2') ;
		
		$imageName  = $this->getApp()->request->post('imageName') ;
		$image  	= $this->getApp()->request->post('image') ;
		$width  	= $this->getApp()->request->post('crop_width') ;
		$height  	= $this->getApp()->request->post('crop_height') ;
		
		$img = new \abeautifulsite\SimpleImage( $this->getFolder() . '/' . $imageName );
		$img
            ->crop($x, $y, $x2, $y2)
            ->resize( $width , $height )
            ->save( $this->getFolder() . '/c/' . $this->updateName( $imageName , $width . "x" . $height ) );
	}
}