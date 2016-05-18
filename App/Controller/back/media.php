<?php

$app->group('/media', function () use ($app) {
	$app->get('/:module', function ( $module ) use ($app) {
		$content = \DB::for_table('module')
								->select('module_id')
								->where_equal('module_class_name', $module)
								->find_one();
		
		$Image = new \App\Kernel\Back\Image([
			'module_id' => $content->module_id
		]);
		$rst = $Image->getAll();
		
		$app->render('media/index.twig.html', [
			'module' => $module,
			'images' => $rst
		]) ;
	});
	
	$app->get('/:module/upload', function ( $module ) use ($app) {
		$app->render('media/upload.twig.html', [
			'module' => $module
		]) ;
	});
});
