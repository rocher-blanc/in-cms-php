<?php

namespace App\Kernel\Back;

require VENODR_PATH . '/blueimp/jquery-file-upload/server/php/UploadHandler.php';

class Upload extends \UploadHandler
{
	function __construct($options = null, $initialize = true, $error_messages = null, $function = null)
	{
        $this->callback = $function ;
        $result = parent::__construct( $options, false, $error_messages ) ;
		
		unset( $this->options['image_versions']['thumbnail'] ) ;
		
		$this->initialize() ;
	}

    protected function initialize()
    {
        parent::initialize();
        die;
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
			if ( is_callable( $this->callback ) )
            {
                $function = $this->callback ;
                $function( $file->name );
            }
        }
        return $file;
    }
}