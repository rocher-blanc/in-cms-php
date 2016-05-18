<?php

$app->group('/security', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
        $security_lock_ip = DB::for_table('param')
            ->where_equal('param_key', 'security_lock_ip')
            ->find_one();

        $security_list_ip = DB::for_table('param')
            ->where_equal('param_key', 'security_list_ip')
            ->find_one();

        if ( $app->request->isPost() )
        {
            if ( ! $security_lock_ip )
            {
                $security_lock_ip = \DB::for_table('param')->create();
                $security_lock_ip->param_key = "security_lock_ip" ;
            }

            if ( $app->request->post('security_list_ip') != '' )
            {
                if ( $app->request->post('security_lock_ip') != NULL && strpos( $app->request->post('security_list_ip') , $app->request()->getIp() ) !== false )
                {
                    $security_lock_ip->param_value = 1;
                }
                else
                {
                    $security_lock_ip->param_value = 0;
                }
            }
            else
            {
                $security_lock_ip->param_value = 0;
            }
            $security_lock_ip->save();

            if ( ! $security_list_ip )
            {
                $security_list_ip = \DB::for_table('param')->create();
                $security_list_ip->param_key = "security_list_ip" ;
            }

            $security_list_ip->param_value = $app->request->post('security_list_ip');
            $security_list_ip->save();

            \App\Kernel\Back\Log::getInstance()->info( 44 ) ;

            $Factory = \App\Kernel\Factory::getInstance() ;
            $Message = \App\Kernel\Message::getInstance() ;

            $Factory->Response()->flashAndRedirect( $Message->get('security_success') , true , 'admin/security' ) ;
        }

        $lock_ip = 0 ;
        $list_ip = "" ;

        if ( $security_lock_ip ) $lock_ip = $security_lock_ip->param_value ;
        if ( $security_list_ip ) $list_ip = $security_list_ip->param_value ;

		$app->render('admin/security/edit.twig.html' , [
            "ip" => $app->request()->getIp(),
            "lock_ip" => $lock_ip,
            "list_ip" => $list_ip
        ]);
	})->name('security_edit')->via('GET', 'POST');
});