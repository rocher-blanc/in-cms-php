<?php

namespace App\Kernel\Back;

use App\Kernel\Common\Image as ImageCommon;

class Image extends ImageCommon
{
	public function getAll()
	{
		return \DB::for_table('media')
			->where_equal('module_id', $this->options['module_id'])
			->find_many();
	}
}