<?php

$app->group('/maintenance', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
        $Container = \App\Kernel\Container::getInstance();

        if ( $app->request->isPost() )
        {
            $Container->param()->set('maintenance_active' , ($app->request->post('maintenance_active') == NULL ? 0 : 1) );
            $Container->param()->set('maintenance_ip' , $app->request->post('maintenance_ip') );

            \App\Kernel\Back\Log::getInstance()->info( 44 ) ;

            $Factory = \App\Kernel\Factory::getInstance() ;
            $Message = \App\Kernel\Message::getInstance() ;

            $Factory->Response()->flashAndRedirect( $Message->get('maintenance_success') , true , 'admin/maintenance' ) ;
        }

		$app->render('admin/maintenance/edit.twig.html' , [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'maintenance_ip' => $Container->param()->get('maintenance_ip'),
            'maintenance_active' => $Container->param()->get('maintenance_active')
        ]);

	})->name('maintenance_edit')->via('GET', 'POST');
});