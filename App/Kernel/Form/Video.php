<?php

namespace App\Kernel\Form;

class Video extends \App\Kernel\Back\Form
{
	public function html( $field, $name, $value = NULL )
	{

        $this->_lib_js  = [
            'cmsmedias/js/jvideo.js'
        ];

        $this->_lib_css = [
            'cmsmedias/css/jvideo.css'
        ];

        $html = '
        <div class="jvideo">
            <div id="video_id_' . $name . '" class="preview">' . ( empty( $value ) ? 'Aucune vidéo' : '' ) . '</div>
        </div>
        <div class="input-group input-group-icon">
            <span class="input-group-addon">
                <span class="icon">
                    <i class="fa fa-link"></i>
                </span>
            </span>
            <input type="text" value="' . $value . '" data-video placeholder="http://youtu.be/xxxxxxx" class="form-control" name="' . $name . '" id="id_' . $name . '"' . ( $field->isRequired() ? ' required="1"' : '' ) . ' />
        </div>' ;

        return $html ;
	}
}