<?php

$app->group('/metadata', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
		if ( strtoupper($req->getMethod()) === 'POST' ) {
			$result = $req->getParsedBody() ;
			
			if ( $result )
			{
				if ( ! array_key_exists( "seo_robots" , $result ) ) $result["seo_robots"] = 0;
				if ( ! array_key_exists( "seo_ssl" , $result ) ) $result["seo_ssl"] = 0;
				if ( ! array_key_exists( "seo_www" , $result ) ) $result["seo_www"] = 0;

				foreach( $result as $key => $value )
				{
					if ( substr( $key , 0 , 4 ) == "seo_" )
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

            \App\Kernel\Back\Log::getInstance()->warning( 41 ) ;
			$Factory->Response()->flashAndRedirect( $Message->get('metadata_success') , true , 'ext/metadata' ) ;
		}
		
		$data = DB::for_table('param')
			->select('param_key')
			->select('param_value')
			->where_like('param_key', 'seo_%')
			->find_many();
		
		$tab = array();
		if ( $data )
		{
			foreach( $data as $row )
			{
				$tab[ $row->param_key ] = $row->param_value ;
			}
		}
		
		return \App\Kernel\AppContext::twig()->render($res, 'ext/metadata/edit.twig.html', [ "post" => $tab ]);

	})->name('metadata_edit');
});