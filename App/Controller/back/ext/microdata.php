<?php

$app->group('/microdata', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
		if ( strtoupper($req->getMethod()) === 'POST' ) {
			$result = $req->getParsedBody() ;

			if ( $result )
			{
				foreach( $result as $key => $value )
				{
					if ( substr( $key , 0 , 3 ) == "md_" )
					{
						$val = $value ;
						$data = \DB::for_table('param')
							->where_equal('param_key', $key)
							->find_one();
						
						if ( ! $data )
						{
							$data = \DB::for_table('param')->create();
							$data->param_key = $key ;
						}
						
						if ( $val === "" ) $val = NULL ;
						$data->param_value = $val ;
						$data->save() ;
					}
				}
			}
			
			$Factory = \App\Kernel\Factory::getInstance() ;
			$Message = \App\Kernel\Message::getInstance() ;

            \App\Kernel\Back\Log::getInstance()->warning( 60 ) ;
			$Factory->Response()->flashAndRedirect( $Message->get('microdata_success') , true , 'ext/microdata' ) ;
		}
		
		$data = DB::for_table('param')
			->select('param_key')
			->select('param_value')
			->where_like('param_key', 'md_%')
			->find_many();
		
		$tab = array();
		if ( $data )
		{
			foreach( $data as $row )
			{
				$tab[ $row->param_key ] = $row->param_value ;
			}
		}
		
		return \App\Kernel\AppContext::twig()->render($res, 'ext/microdata/edit.twig.html', [ "post" => $tab ]);

	})->name('microdata_edit');
});