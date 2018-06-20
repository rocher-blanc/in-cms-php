<?php

namespace App\Kernel\Back;

use App\Kernel\Back\Image;

class Media extends \App\Kernel\Common\Media
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private $module_id = NULL ;
	private $module_name = NULL ;
	private $folder_name = NULL ;

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

	public function setFolder( $var )
	{
		$this->folder_name = $var ;
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
	
	public function getFolder()
	{
		return $this->folder_name ;
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
				$std->media_mini_name   = $this->getMiniName( $row->media_name ) ;
				
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
			$rst->mini = $this->Factory()->Url()->get( $entity->getPathImage( false ) . '/t/' . $rst->mini , true );

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
	
	public function rename()
	{
		$path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
		$img  = UPLOAD_PATH . '/' . $this->getImageName() ;
		
		if ( file_exists( $img ) )
		{
			$name = $this->updateName( $this->getImageName() ) ;
			
			if ( file_exists( $path . $name ) ) $exist = true ;
			else							    $exist = false ;
			
			if ( $exist == true )
			{
				$i = 1;
				if ( strpos( "-" , $name ) !== false )
				{
					$exp 	= explode( "-" , $name ) ;
					$ct  	= count( $exp ) ;
					$ext 	= $exp[ $ct - 1 ] ;
					$extlen = ( strlen( $ext ) + 1 ) * -1 ;
					
					$exp = str_replace( $this->getExtension( $name ) , "" , $name ) ;
					
					if ( is_numeric( $ext ) )
					{
						$name = substr( $name , 0 , $extlen ) . $this->getExtension( $name ) ;
						$i    = intval( $ext + 1 ) ;
					}
					else
					{
						$name = $this->updateName( $this->getImageName() ) ;
					}
				}
				
				while( $exist == true )
				{
					$newname = $this->updateName( $name , $i ) ;
					if ( ! file_exists( $path . $newname ) )
					{
						$exist = false ;
						$name  = $newname ;
					}
					$i++;
				}
			}
			
			if ( $path . $name != $img )
			{
				rename( $img , $path . $name ) ;
				
				$media = \DB::for_table('media')
					->where_id_is( $this->getImageId() )
					->find_one();
				
				$media->media_name = $name;
				$media->save() ;
				
				$this->setImageName( $name ) ;
			}
			
			return $this->genThumb( 100 , 100 ) ;
		}
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
	
	private function updateName( $name , $addStr = "" )
	{
		$exp 	= explode( "." , $name ) ;
		$ext 	= end( $exp ) ;
		$extlen = ( strlen( $ext ) + 1 ) * -1 ;
		if ( $addStr != "" ) $addStr = "-" . $addStr ;
		
		$name = substr( $name , 0 , $extlen ) ;
		$name = $this->Factory()->Url()->encode( $name . $addStr ) . "." . $ext ;
		
		return $name ;
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