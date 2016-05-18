<?php

$app->group('/theme', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
        $data = DB::for_table('param')
            ->where_equal('param_key', 'admin_color')
            ->find_one();

        if ( $app->request->isPost() )
        {
            if ( ! $data )
            {
                $data = \DB::for_table('param')->create();
                $data->param_key = "admin_color" ;
            }

            $data->param_value = $app->request->post('admin_color');
            $data->save();

            \App\Kernel\Back\Log::getInstance()->info( 44 ) ;

            $Factory = \App\Kernel\Factory::getInstance() ;
            $Message = \App\Kernel\Message::getInstance() ;

            $Factory->Response()->flashAndRedirect( $Message->get('theme_success') , true , 'admin/theme' ) ;
        }

        $value = "" ;

        if ( $data ) $value = $data->param_value ;

		$app->render('admin/theme/edit.twig.html' , [

        ]);

	})->name('theme_edit')->via('GET', 'POST');

    $app->post('/logo', function () {
        $upload_dir 	= UPLOAD_PATH . '/' ;
        $upload_url 	= str_replace( WEB_PATH , '' , $upload_dir ) ;
        $upload_handler = new \App\Kernel\Back\Upload([
            'upload_dir' => $upload_dir,
            'param_name' => 'files'
        ], true, null, function( $file ) {
            rename( UPLOAD_PATH . '/' . $file , ASSETS_IMG_PATH . '/' . $file );

            $data = DB::for_table('param')
                ->where_equal('param_key', 'admin_logo')
                ->find_one();

            if ( ! $data )
            {
                $data = \DB::for_table('param')->create();
                $data->param_key = "admin_logo" ;
            }

            $data->param_value = str_replace( WEB_PATH , '' , ASSETS_IMG_PATH . '/' . $file );
            $data->save();

            \App\Kernel\Factory::getInstance()->Response()->returnJSON( "Le logo de l'interface d'administration a été modifié" , true );
        });
    });
});