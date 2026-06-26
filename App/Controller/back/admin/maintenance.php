<?php

$app->group('/maintenance', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
        $Container = \App\Kernel\Container::getInstance();

        if ( strtoupper($req->getMethod()) === 'POST' )
        {
            $Container->param()->set('maintenance_active' , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['maintenance_active'] ?? '') : '') );
            $Container->param()->set('maintenance_ip' , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['maintenance_ip'] ?? '') : '') );

            \App\Kernel\Back\Log::getInstance()->info( 44 ) ;

            $Factory = \App\Kernel\Factory::getInstance() ;
            $Message = \App\Kernel\Message::getInstance() ;

            $Factory->Response()->flashAndRedirect( $Message->get('maintenance_success') , true , 'admin/maintenance' ) ;
        }

		return \App\Kernel\AppContext::twig()->render($res, 'admin/maintenance/edit.twig.html', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'maintenance_ip' => $Container->param()->get('maintenance_ip'),
            'maintenance_active' => $Container->param()->get('maintenance_active')
        ]);

	})->name('maintenance_edit');
});