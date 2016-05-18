<?php

$app->group('/group', function () use ($app)
{
	$app->get('/', function () use ($app) {

		$contentRows = \DB::for_table('user_group') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$contentRows = $contentRows->where_not_equal('user_group_id' , 1 ) ;
		}
		
		$contentRows = $contentRows->order_by_asc('user_group_name')
								   ->find_many();
		
		$app->render('ext/group/index.twig.html', array( "contentRows" => $contentRows ));

	})->name('group_index');

	$app->map('/edit(/:id)', function ($id = -1) use ($app)
	{
		if ( $id == 1 )
		{
			$Guard = new \App\Kernel\Back\Acl;
			if ( $Guard->isAdmin() == false ) $app->redirect( $app->config('forbidden.url') ) ;
		}
		
		$error    = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('user_group')
			->where(array('user_group_id' => $id))
			->find_one();

		if ( $id != -1 && !$contentRow ) {
			$app->redirect( $app->config('admin.url') . '/ext/group');
		}
		else {
			$post = $contentRow ;
		}
		
		if ( $app->request->isPost() ) {
			$post = array(
				"user_group_name" => $app->request->post('user_group_name')
			) ;
			
			if ( $app->request->post('user_group_name') == "" ) {
				$error = true ;
				$tabError['user_group_name'] = "Veuillez remplir ce champ" ;
			}
			else {
				$exist = \DB::for_table('user_group')->where_equal('user_group_name' , $app->request->post('user_group_name'));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_group_id' , $id);
				$exist = $exist->count();
			}
			
			if ( $app->request->post('user_group_name') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_name'] = "Ce nom de groupe est deja utilisé" ;
			}
			
			if ( $error == false ) {
				if ( !$contentRow ) {
					$contentRow = DB::for_table('user_group')->create();
					$contentRow->user_group_url 	 = "/.+;/" ;
					$contentRow->user_group_redirect = "/admin/";
					$add = true ;
				}
				
				$contentRow->user_group_name = $app->request->post('user_group_name');
				$contentRow->save();
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 7 : 8 ) , $contentRow->user_group_name ) ;
				
				$id = $contentRow->user_group_id ;
				
				$app->flash('__msg',addslashes( json_encode( "Le groupe a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
				$app->flash('__result',true);
				
				if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/group/edit/' . $id );
				else 											$app->redirect( $app->config('admin.url') . '/ext/group' );
			}
		}
		
		if ( $id != -1 ) {
			$users = \DB::for_table('user')
				->select('user_name')
				->select('user_id')
				->where(array('user_group_id' => $id))
				->find_many();
			
			$extension = \DB::for_table('extension')
				->select('extension_id')
				->select('extension_name')
				->select('extension_technical_name')
				->select('extension_perm_add')
				->select('extension_perm_update')
				->select('extension_perm_delete')
				->find_many();
			
			$tabExt = array() ;
			foreach( $extension as $row ) {
				$std = new stdClass;
				$std->perm_add   	 = $row->extension_perm_add ;
				$std->perm_update    = $row->extension_perm_update ;
				$std->perm_delete    = $row->extension_perm_delete ;
				
				$std->extension_id   		   = $row->extension_id ;
				$std->extension_name 		   = $row->extension_name ;
				$std->extension_technical_name = $row->extension_technical_name ;
				
				$Guard = new \App\Kernel\Back\Acl;
				$Guard->setExtension( $row->extension_technical_name );
				$Guard->setGroupId( $id );
				$Guard->load();
				
				$std->add 	 = $Guard->checkAdd() ;
				$std->update = $Guard->checkUpdate() ;
				$std->delete = $Guard->checkDelete() ;
				
				$tabExt[] = $std ;
			}
			
			$module = \DB::for_table('module')
				->select('module_name')
				->select('module_class_name')
				->select('module_id')
				->find_many();
			
			$tabModule = array() ;
			foreach( $module as $row )
			{
				if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $row->module_class_name ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Back\\" . ucfirst( $row->module_class_name );
				else																			 				 $ControllerClass = '\\' . APP_NAME . '\Kernel\Back\Controller' ;
				
				$Controller = new $ControllerClass;
				$Controller->setEntityName( $row->module_class_name );
				if ( $Controller->loadEntity() == true )
				{
					$std = new \stdClass;
					$std->module_id   		   = $row->module_id ;
					$std->module_name 		   = $row->module_name ;
					$std->module_class_name    = $row->module_class_name ;
					
					$std->perm_add   	 	= $Controller->getEntity()->hasAction('add') ;
					$std->perm_update    	= $Controller->getEntity()->hasAction('edit') ;
					$std->perm_delete    	= $Controller->getEntity()->hasAction('delete') ;
					$std->perm_validation   = ( $Controller->getEntity()->hasAction('up') && $Controller->getEntity()->hasAction('down') ) ;
					$std->perm_config		= $Controller->getEntity()->hasAction('config') ;
					
					$Guard = new \App\Kernel\Back\Acl;
					$Guard->setModule( $row->module_class_name );
					$Guard->setGroupId( $id );
					$Guard->load();
					
					$std->add 	 	 = $Guard->checkAdd() ;
					$std->update 	 = $Guard->checkUpdate() ;
					$std->delete 	 = $Guard->checkDelete() ;
					$std->validation = $Guard->checkValidation() ;
					$std->config 	 = $Guard->checkConfig() ;
					
					$tabModule[] = $std ;
				}
			}
		}
		else {
			$users 		= array() ;
			$extension  = array() ;
		}

		$app->render('ext/group/edit.twig.html', array(
												"contentRow" => $contentRow,
												"id" 		 => $id,
												"users" 	 => $users,
												"extension"  => $tabExt,
												"module" 	 => $tabModule,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->name('group_edit')->via('GET', 'POST');

	$app->get('/right', function () use ($app)
	{
		if ( $app->request->post( $app->config('token') ) == $_SESSION[ $app->config('token') ] )
		{
			$Guard = new \App\Kernel\Back\Acl;
					
			if ( $app->request->post('ext') == '' && $app->request->post('module') != '' )
			{
				$Guard->setModule( $app->request->post('module') );
				$extRow = \DB::for_table('module')
					->select('module_name')
					->where(array('module_class_name' => $app->request->post('module')))
					->find_one();
				$value = $extRow->module_name ;
			}
			else if ( $app->request->post('ext') != '' && $app->request->post('module') == '' )
			{
				$Guard->setExtension( $app->request->post('ext') );
				$extRow = \DB::for_table('extension')
					->select('extension_name')
					->where(array('extension_technical_name' => $app->request->post('ext')))
					->find_one();
				$value = $extRow->extension_name ;
			}
			
			$Guard->setGroupId( $app->request->post('group') );
			$Guard->load();
			
			$groupRow = \DB::for_table('user_group')
				->select('user_group_name')
				->where(array( 'user_group_id' => $app->request->post('group') ))
				->find_one();
			
			$valueLog = $groupRow->user_group_name . " - " . $value ;
			
			switch( $app->request->post('right') )
			{
				case "add" :
					$Guard->updateAdd();
				break;
				case "update" :
					$Guard->updateUpdate();
				break;
				case "delete" :
					$Guard->updateDelete();
				break;
				case "validation" :
					$Guard->updateValidation();
				break;
				case "config" :
					$Guard->updateConfig();
				break;
			}
		}
		else
		{
			$msg = "Le token de sécurité est invalide";
			$ret = false;
		}
		
		echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
	})->name('group_right')->via('GET', 'POST');

	$app->delete('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		if ( $id == $app->environment['user']['group_id'] )
		{
			$msg = "Vous ne pouvez pas supprimer le groupe dans lequel vous êtes actuellement présent!" ;
		}
		elseif ( $id == 1 )
		{
			$msg = "Impossible de supprimer ce groupe pour des raisons techniques!" ;
		}
		else 
		{
			$contentRow = \DB::for_table('user_group')
				->where_equal('user_group_id' , $id)
				->where_not_equal('user_group_id' , $app->environment['user']['group_id'] )
				->find_one();
			
			if ( $contentRow )
			{
				\App\Kernel\Back\Log::getInstance()->warning( 6 , $contentRow->user_group_name ) ;
				
				$permission = \DB::for_table('permission')
					->where_equal('permission_group_id' , $id)
					->delete_many();
				
				$msg = "Le groupe a bien été supprimé" ;
				$ret = true ;
				$contentRow->delete();
			}
			else
			{
				$msg = "Une erreur est survenue lors de la suppression" ;
			}

		}
		
		echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
	})->name('group_delete');
});