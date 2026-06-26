<?php

use App\Kernel\Front\Translate;

$app->group('/group', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {

		$contentRows = \DB::for_table('user_group') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$contentRows = $contentRows->where_not_equal('user_group_id' , 1 ) ;
		}
		
		$contentRows = $contentRows->order_by_asc('user_group_name')
								   ->find_many();
		
		return \App\Kernel\AppContext::twig()->render($res, 'ext/group/index.twig.html', array( "contentRows" => $contentRows ));

	})->name('group_index');

	$app->map(['GET', 'POST'], '/edit[/{id}]', function ($id = -1) use ($app)
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
		
		if ( strtoupper($req->getMethod()) === 'POST' ) {
			$post = array(
				"user_group_name" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_group_name'] ?? '') : '')
			) ;
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_group_name'] ?? '') : '') == "" ) {
				$error = true ;
				$tabError['user_group_name'] = Translate::getInstance()->getText( 'mandatory_fillin' ) ;
			}
			else {
				$exist = \DB::for_table('user_group')->where_equal('user_group_name' , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_group_name'] ?? '') : ''));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_group_id' , $id);
				$exist = $exist->count();
			}
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_group_name'] ?? '') : '') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_name'] = Translate::getInstance()->getText( 'msg_grp_name_error' );
			}
			
			if ( $error == false ) {
				if ( !$contentRow ) {
					$contentRow = DB::for_table('user_group')->create();
					$add = true ;
				}
				
				$contentRow->user_group_name = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_group_name'] ?? '') : '');
				$contentRow->save();
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 7 : 8 ) , $contentRow->user_group_name ) ;
				
				$id = $contentRow->user_group_id ;

                \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect( Translate::getInstance()->getText('msg_grp_name_part') . ( $add == true ? Translate::getInstance()->getText('added') : Translate::getInstance()->getText('modified') ) , true , '/ext/group' );
			}
			else
            {

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
					$std->perm_validation   = ( $Controller->getEntity()->hasAction('enable') && $Controller->getEntity()->hasAction('disable') ) ;
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

		return \App\Kernel\AppContext::twig()->render($res, 'ext/group/edit.twig.html', array(
												"contentRow" => $contentRow,
												"id" 		 => $id,
												"users" 	 => $users,
												"extension"  => $tabExt,
												"module" 	 => $tabModule,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->name('group_edit');

	$app->get('/right', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
		$csrfKey = \App\Kernel\Config::getInstance()->get('token', 'csrf_token');
		if ( ($req->getParsedBody()[$csrfKey] ?? '') == ($_SESSION[$csrfKey] ?? '') )
		{
			$Guard = new \App\Kernel\Back\Acl;
					
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['ext'] ?? '') : '') == '' && (is_array($req->getParsedBody()) ? ($req->getParsedBody()['module'] ?? '') : '') != '' )
			{
				$Guard->setModule( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['module'] ?? '') : '') );
				$extRow = \DB::for_table('module')
					->select('module_name')
					->where(array('module_class_name' => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['module'] ?? '') : '')))
					->find_one();
				$value = $extRow->module_name ;
			}
			else if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['ext'] ?? '') : '') != '' && (is_array($req->getParsedBody()) ? ($req->getParsedBody()['module'] ?? '') : '') == '' )
			{
				$Guard->setExtension( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['ext'] ?? '') : '') );
				$extRow = \DB::for_table('extension')
					->select('extension_name')
					->where(array('extension_technical_name' => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['ext'] ?? '') : '')))
					->find_one();
				$value = $extRow->extension_name ;
			}
			
			$Guard->setGroupId( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['group'] ?? '') : '') );
			$Guard->load();
			
			$groupRow = \DB::for_table('user_group')
				->select('user_group_name')
				->where(array( 'user_group_id' => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['group'] ?? '') : '') ))
				->find_one();
			
			$valueLog = $groupRow->user_group_name . " - " . $value ;
			
			switch( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['right'] ?? '') : '') )
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
			$msg = Translate::getInstance()->getText( 'msg_token_invalide' );
			$ret = false;
		}
		
		echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
	})->name('group_right');

    $app->get('/delete/:id', function ($id) use ($app) {
        return \App\Kernel\AppContext::twig()->render($res, 'common/delete.twig', [
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/group/delete/' . $id)
        ]);
    });

	$app->post('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		if ( $id == $app->environment['user']['group_id'] )
		{
		    $msg = Translate::getInstance()->getText( 'delete_groupe' );
		}
		elseif ( $id == 1 )
		{
            $msg = Translate::getInstance()->getText( 'delete_groupe_technical' );
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

				$msg = Translate::getInstance()->getText( 'delete_groupe_success' );
				$ret = true ;
				$contentRow->delete();
			}
			else
			{
			    $msg = Translate::getInstance()->getText( 'delete_error' );
			}

		}
		
		echo json_encode( array( "msg" => $msg , "result" => $ret , "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/group') ) ) ;
	})->name('group_delete');
});