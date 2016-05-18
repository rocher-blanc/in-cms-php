<?php

namespace App\Kernel\Back;

require VENODR_PATH . '/blueimp/jquery-file-upload/server/php/UploadHandler.php';

class Image extends \UploadHandler
{
	function __construct($options = null, $initialize = true, $error_messages = null)
	{
        $result = parent::__construct( $options, false, $error_messages ) ;
		
		unset( $this->options['image_versions']['thumbnail'] ) ;
		
		$this->error_messages['min_width']  = 'L\'image doit avoir une largeur minimum de ' . $this->options['min_width'] . 'px' ;
        $this->error_messages['min_height'] = 'L\'image doit avoir une hauteur minimum de ' . $this->options['min_height'] . 'px' ;
		
		$this->initialize() ;
	}
	
	protected function initialize()
	{
        parent::initialize();
		die;
    }
	
	public function getAll()
	{
		return \DB::for_table('media')
			->where_equal('module_id', $this->options['module_id'])
			->find_many();
	}
	
    protected function handle_form_data($file, $index)
	{
        $file->title = @$_REQUEST['title'][$index];
        $file->description = @$_REQUEST['description'][$index];
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
	{
        $file = parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);
        if (empty($file->error))
		{
			$media = \DB::for_table('media')->create() ;
            $media->media_name 		= $file->name;
            $media->media_size 		= $file->size;
            $media->media_type 		= $file->type;
            $media->media_module_id = $this->options['module_id'];
            $media->save() ;
			
			$file->id = $media->media_id ;
        }
        return $file;
    }
}