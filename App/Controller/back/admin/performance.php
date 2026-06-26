<?php

use App\Kernel\AppContext;
use App\Kernel\Factory;
$app->group('/performance', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
        $data = DB::for_table('param')
            ->where_equal('param_key', 'server_cdn')
            ->find_one();

        if ( strtoupper($req->getMethod()) === 'POST' )
        {
            if ( ! $data )
            {
                $data = \DB::for_table('param')->create();
                $data->param_key = "server_cdn" ;
            }

            $data->param_value = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['server_cdn'] ?? '') : '');
            $data->save();

            \App\Kernel\Back\Log::getInstance()->warning( 40 , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['server_cdn'] ?? '') : '') ) ;
			
			$Factory = \App\Kernel\Factory::getInstance() ;
            $Message = \App\Kernel\Message::getInstance() ;

			$Factory->Response()->flashAndRedirect( $Message->get('performance_success') , true , 'admin/performance' ) ;
		}

        $value = "" ;

        if ( $data ) $value = $data->param_value ;
		
		return \App\Kernel\AppContext::twig()->render($res, 'admin/performance/edit.twig.html', array(
            "cdn" => $value,
            "dev" => \App\Kernel\CMS::getInstance()->isDev(),
		));

	})->setName('performance_edit');
});