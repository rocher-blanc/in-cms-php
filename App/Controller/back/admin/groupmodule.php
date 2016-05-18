<?php

$app->group('/groupmodule', function () use ($app)
{
	$app->get('/', function () use ($app)
	{

		$contentRows = \DB::for_table('module_group')
								->order_by_asc('module_group_order')
								->find_many();
		
		$app->render('admin/groupmodule/index.twig.html', array( 
																	"contentRows" => $contentRows,
																	"moduleNoGroupRows" => $moduleNoGroupRows,
																	"moduleGroupRows" => $contentRows,
															));

	})->name('groupmodule_index');
	
	$app->get('/bygroup(/:id)', function ( $id = NULL ) use ($app)
	{
		$contentRows = \DB::for_table('module');
		
		if ( $id !== NULL ) 	$contentRows = $contentRows->where_equal('module_module_group_id',$id) ;
		else					$contentRows = $contentRows->where_null('module_module_group_id') ;
			
		$contentRows = $contentRows->order_by_asc('module_order')
									->find_many();
		
		$app->render('admin/groupmodule/module.twig.html', array( "contentRows" => $contentRows ));

	})->name('groupmodule_module_by_group');
	
	$app->get('/updateOrder/:idgroup/(:order)', function ( $idgroup , $order = NULL ) use ($app)
	{
		if ( $order === NULL )
		{
			echo '0x0' ;
		}
		else
		{
			if ( $idgroup == 0 ) $idgroup = NULL ;
			if ( strpos( $order , ',' ) !== false )
			{
				$exp = explode( "," , $order ) ;
				$i   = 1;
				foreach( $exp as $row ) {
					list( $null , $id ) = explode( '-' , $row ) ;
					$contentRows = \DB::for_table('module')
								->where_id_is( $id )
								->find_one();
					$contentRows->module_module_group_id = $idgroup ;
					$contentRows->module_order = $i ;
					$contentRows->save();
				}
			}
			else
			{
				list( $null , $id ) = explode( "-" , $order ) ;
				$contentRows = \DB::for_table('module')
								->where_id_is( $id )
								->find_one();
				$contentRows->module_module_group_id = $idgroup ;
				$contentRows->module_order = 1 ;
				$contentRows->save();
			}
		}
		
		$contentRows = \DB::for_table('module');
		
		if ( $id !== NULL ) 	$contentRows = $contentRows->where_equal('module_module_group_id',$id) ;
		else					$contentRows = $contentRows->where_null('module_module_group_id') ;
			
		$contentRows = $contentRows->order_by_asc('module_order')
									->find_many();
		
		$app->render('admin/groupmodule/module.twig.html', array( "contentRows" => $contentRows ));

	})->name('groupmodule_module_by_group')->via('GET', 'POST');

	$app->delete('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		$contentRow = \DB::for_table('module_group')
			->where_equal('module_group_id' , $id)
			->find_one();
		
		if ( $contentRow )
		{
			$content = \DB::for_table('module_group')
					->where_gt('module_group_order' , $contentRow->module_group_order)
					->find_many();
			if ( $content )
			{
				foreach( $content as $row )
				{
					$row->module_group_order--;
					$row->save();
				}
			}
			
			$module = \DB::for_table('module')
					->where_equal('module_module_group_id' , $id)
					->find_many();
			if ( $module )
			{
				foreach( $module as $row )
				{
					$row->module_module_group_id = NULL;
					$row->save();
				}
			}
			
			\App\Kernel\Back\Log::getInstance()->warning( 22 , $contentRow->module_group_name ) ;
			
			$msg = "Le groupe de modules a bien été supprimé" ;
			$ret = true ;
			$contentRow->delete();
		}
		else
		{
			$msg = "Une erreur est survenue lors de la suppression" ;
		}
		
		echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
	})->name('groupmodule_delete');
	
	$app->get('/active/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ $app->config('token') ] ) {
			$module = \DB::for_table('module_group')
							->where_equal('module_group_id' , $id)
							->find_one();
			
			if ( $module->module_group_active == 0 ) {
				$module->module_group_active = 1;
				$module->save();
				
				\App\Kernel\Back\Log::getInstance()->warning( 25 , $module->module_group_name ) ;
						
				$msg = "Le groupe de modules a bien été activé";
				$ret = true;
			}
			else {
				$msg = "Impossible, le groupe de modules est déja activé";
				$ret = false;
			}
		}
		else {
			$msg = "Le token de sécurité est invalide";
			$ret = false;
		}
		
		$app->flash('__msg',addslashes( json_encode( $msg )));
		$app->flash('__result',$ret);
		$app->redirect( $app->config('admin.url') . '/admin/groupmodule' );
	})->name('groupmodule_active');
	
	$app->get('/disactive/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ $app->config('token') ] ) {
			$module = \DB::for_table('module_group')
							->where_equal('module_group_id' , $id)
							->find_one();
			
			if ( $module->module_group_active == 1 ) {
				$module->module_group_active = 0;
				$module->save();
				
				\App\Kernel\Back\Log::getInstance()->warning( 26 , $module->module_group_name ) ;
						
				$msg = "Le groupe de modules a bien été désactivé";
				$ret = true;
			}
			else {
				$msg = "Impossible, le groupe de modules est déja désactivé";
				$ret = false;
			}
		}
		else {
			$msg = "Le token de sécurité est invalide";
			$ret = false;
		}
		
		$app->flash('__msg',addslashes( json_encode( $msg )));
		$app->flash('__result',$ret);
		$app->redirect( $app->config('admin.url') . '/admin/groupmodule' );
	})->name('groupmodule_disactive');
	
	$app->map('/edit(/:id)', function ($id = -1) use ($app)
	{
		$error 	  = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('module_group')
			->where_equal('module_group_id' , $id)
			->find_one();
		
		if ( $id != -1 && !$contentRow ) {
			$app->redirect( $app->config('admin.url') . '/admin/groupmodule');
		}
		
		if ( $app->request->isPost() ) {
			$post = array(
				"module_group_name" => $app->request->post('module_group_name'),
				"module_group_icon" => $app->request->post('module_group_icon'),
				"module_group_active" => $app->request->post('module_group_active')
			) ;
			
			if ( !$contentRow ) {
				$contentRow = \DB::for_table('module_group')->create();
				$add = true ;
			}
			
			if ( $app->request->post('module_group_name') == "" ) {
					$error = true ;
					$tabError['module_group_name'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $app->request->post('module_group_icon') == "" ) {
					$error = true ;
					$tabError['module_group_icon'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $error == false ) {
				$contentRow->module_group_name 		= $app->request->post('module_group_name') ;
				$contentRow->module_group_icon 		= $app->request->post('module_group_icon') ;
				$contentRow->module_group_active 	= ( $app->request->post('module_group_active') == NULL ? 0 : 1 ) ;
				if ( $add == true )
				{
					$contentRow->module_group_order = \DB::for_table('module_group')->max('module_group_order') + 1;
				}
				$contentRow->save() ;
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 23 : 24 ) , $contentRow->module_group_name ) ;
				
				$id = $contentRow->module_group_id;
				
				$app->flash('__msg',addslashes( json_encode( "Le groupe de modules a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
				$app->flash('__result',true);
				
				if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/admin/groupmodule/edit/' . $id );
				else 											$app->redirect( $app->config('admin.url') . '/admin/groupmodule' );
			}
		}
		else {
			$post = $contentRow ;
		}
		
		$app->render('admin/groupmodule/edit.twig.html', array( 
			"post" => $post,
			"id" => $id,
			"error"		 => ( $error === false ? "0" : "1" ),
			"tabError"	 => json_encode( $tabError )
		));
	})->name('groupmodule_edit')->via('GET', 'POST');
	
	$app->group('/order', function () use ($app)
	{
		$app->get('/up/:id/:token', function ($id,$token) use ($app)
		{
			if ( $token == $_SESSION[ $app->config('token') ] ) {
				$modgroup = \DB::for_table('module_group')
							->where_equal('module_group_id' , $id)
							->find_one();
				
				if ( $modgroup->module_group_order != 1 ) {
					$sup = $modgroup->module_group_order - 1;
					$modgroupSup = \DB::for_table('module_group')
									->where_equal('module_group_order' , $sup)
									->find_one();
					$modgroupSup->module_group_order = $modgroup->module_group_order;
					$modgroupSup->save();
					
					$modgroup->module_group_order = $sup;
					$modgroup->save();
					
					\App\Kernel\Back\Log::getInstance()->warning( 27 , $modgroup->module_group_name ) ;
					
					$msg = 'La position du groupe de modules a été modifiée';
					$ret = true;
				}
				else {
					$msg = 'Impossible, le groupe de modules est déja au niveau le plus haut';
					$ret = false;
				}
			}
			else {
				$msg = "Le token de sécurité est invalide";
				$ret = false;
			}
		
			$app->flash('__msg',addslashes( json_encode( $msg )));
			$app->flash('__result',$ret);
			$app->redirect( $app->config('admin.url') . '/admin/groupmodule' );
		})->name('groupmodule_up');

		$app->get('/down/:id/:token', function ($id,$token) use ($app)
		{
			if ( $token == $_SESSION[ $app->config('token') ] ) {
				$max = \DB::for_table('module_group')->max('module_group_order');
				
				$modgroup = \DB::for_table('module_group')
							->where_equal('module_group_id' , $id)
							->find_one();
					
					if ( $modgroup->module_group_order < $max ) {
						$sup = $modgroup->module_group_order + 1;
						$modgroupSup = \DB::for_table('module_group')
										->where_equal('module_group_order' , $sup)
										->find_one();
						$modgroupSup->module_group_order = $modgroup->module_group_order;
						$modgroupSup->save();
						
						$modgroup->module_group_order = $sup;
						$modgroup->save();
						
						\App\Kernel\Back\Log::getInstance()->warning( 28 , $modgroup->module_group_name ) ;
						
						$msg = 'La position du groupe de modules a été modifiée';
						$ret = true;
					}
					else {
						$msg = 'Impossible, le groupe de modules est déja au niveau le plus bas' . $modgroup->module_group_order;
						$ret = false;
					}
			}
			else {
				$msg = "Le token de sécurité est invalide";
				$ret = false;
			}
		
			$app->flash('__msg',addslashes( json_encode( $msg )));
			$app->flash('__result',$ret);
			$app->redirect( $app->config('admin.url') . '/admin/groupmodule' );
		})->name('groupmodule_down');

	});
});