<?php

$app->group('/rgpd', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
		if ( $app->request->isPost() ) {
			$result = $app->request->post() ;
			
			if ( $result )
			{
				if ( ! array_key_exists( "rgpd_enabled" , $result ) ) $result["rgpd_enabled"] = 0;

				foreach( $result as $key => $value )
				{
					if ( substr( $key , 0 , 5 ) == "rgpd_" )
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

            \App\Kernel\Back\Log::getInstance()->warning( 61 ) ;
			$Factory->Response()->flashAndRedirect( $Message->get('rgpd_success') , true , 'ext/rgpd' ) ;
		}
		
		$data = DB::for_table('param')
			->select('param_key')
			->select('param_value')
			->where_like('param_key', 'rgpd_%')
			->find_many();
		
		$tab = array();
		if ( $data )
		{
			foreach( $data as $row )
			{
				$tab[ $row->param_key ] = $row->param_value ;
			}
		}
		
		$app->render('ext/rgpd/edit.twig.html' ,[ "post" => $tab ]);

	})->name('rgpd_edit')->via('GET', 'POST');
});