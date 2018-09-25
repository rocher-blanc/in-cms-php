<?php

namespace App\Kernel\Common;

class Image
{
	public function __construct( $options )
	{
		$this->options = $options ;
	}

    public function upload()
	{
		$name       = basename($_FILES[ $this->options['param_name'] ]["name"]);
		$ext        = explode( '.' , $name );
		$extension  = end( $ext );
		$name       = basename( $name , '.' . $extension );
		$name       = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;

		$rst = move_uploaded_file( $_FILES[ $this->options['param_name'] ]["tmp_name"] , $this->options['upload_dir'] . "/" . $name );

        if ( $rst !== false )
		{
			$media = \DB::for_table('media')->create() ;
            $media->media_name 		= $name;
            $media->media_size 		= $_FILES[ $this->options['param_name'] ]["size"];
            $media->media_type 		= $_FILES[ $this->options['param_name'] ]["type"];
            $media->media_module_id = $this->options['module_id'];
            $media->media_field     = $this->options['field'];
			$media->save() ;

			$std = new \stdClass;
			$std->id = $media->media_id;
			$std->key = $media->media_id;
			$std->field = $this->options['param_name'];

			return $std;
        }
        else
		{
			return NULL ;
		}
    }
}