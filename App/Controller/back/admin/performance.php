<?php

$app->group('/performance', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
        $data = DB::for_table('param')
            ->where_equal('param_key', 'server_cdn')
            ->find_one();

        if ( $app->request->isPost() )
        {
            if ( ! $data )
            {
                $data = \DB::for_table('param')->create();
                $data->param_key = "server_cdn" ;
            }

            $data->param_value = $app->request->post('server_cdn');
            $data->save();

            \App\Kernel\Back\Log::getInstance()->warning( 40 , $app->request->post('server_cdn') ) ;
			
			$Factory = \App\Kernel\Factory::getInstance() ;
            $Message = \App\Kernel\Message::getInstance() ;

			$Factory->Response()->flashAndRedirect( $Message->get('performance_success') , true , 'admin/performance' ) ;
		}

        $value = "" ;

        if ( $data ) $value = $data->param_value ;
		
		$app->render('admin/performance/edit.twig.html' , array(
            "cdn" => $value,
            "dev" => \App\Kernel\CMS::getInstance()->isDev(),
		));

	})->name('performance_edit')->via('GET', 'POST');
});