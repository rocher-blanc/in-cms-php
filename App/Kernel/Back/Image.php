<?php

namespace App\Kernel\Back;

class Image extends \App\Kernel\Common\Image
{
	public function getAll()
	{
		return \DB::for_table('media')
			->where_equal('module_id', $this->options['module_id'])
			->find_many();
	}
}