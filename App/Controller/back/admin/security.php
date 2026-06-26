<?php

$app->group('/security', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
        $security_lock_ip = DB::for_table('param')
            ->where_equal('param_key', 'security_lock_ip')
            ->find_one();

        $security_list_ip = DB::for_table('param')
            ->where_equal('param_key', 'security_list_ip')
            ->find_one();

        if ( strtoupper($req->getMethod()) === 'POST' )
        {
            if ( ! $security_lock_ip )
            {
                $security_lock_ip = \DB::for_table('param')->create();
                $security_lock_ip->param_key = "security_lock_ip" ;
            }

            if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['security_list_ip'] ?? '') : '') != '' )
            {
                if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['security_lock_ip'] ?? '') : '') != NULL && strpos( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['security_list_ip'] ?? '') : '') , ($_SERVER['REMOTE_ADDR'] ?? '') ) !== false )
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

            $security_list_ip->param_value = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['security_list_ip'] ?? '') : '');
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

		return \App\Kernel\AppContext::twig()->render($res, 'admin/security/edit.twig.html', [
            "ip" => ($_SERVER['REMOTE_ADDR'] ?? ''),
            "lock_ip" => $lock_ip,
            "list_ip" => $list_ip
        ]);
	})->name('security_edit');
});