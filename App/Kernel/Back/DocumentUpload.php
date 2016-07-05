<?php

namespace App\Kernel\Back;

require VENDOR_PATH . '/blueimp/jquery-file-upload/server/php/UploadHandler.php';

class DocumentUpload extends \UploadHandler
{
	function __construct($options = null, $initialize = true, $error_messages = null)
	{
        $result = parent::__construct( $options, false, $error_messages ) ;
		
		unset( $this->options['image_versions']['thumbnail'] ) ;
		
		$this->initialize() ;
	}
	
	protected function initialize()
	{
        parent::initialize();
		die; // ne pas toucher
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
			$document = \DB::for_table('document')->create() ;
            $document->document_name 		= $file->name;
            $document->document_size 		= $file->size;
            $document->document_type 		= $file->type;
            $document->document_module_id 	= $this->options['module_id'];
            $document->save() ;
			
			$file->id = $document->document_id ;
        }
        return $file;
    }
}