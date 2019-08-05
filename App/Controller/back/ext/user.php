<?php

use App\Kernel\Front\Translate;

$app->group('/user', function () use ($app)
{
	$app->get('/', function () use ($app) {

		$contentRows = \DB::for_table('user') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$contentRows = $contentRows->where_not_equal('user_group_id' , 1 ) ;
		}
		
		$contentRows = $contentRows->order_by_asc('user_id')
								   ->find_many();
		
		$app->render('ext/user/index.twig.html', array( "contentRows" => $contentRows ));

	})->name('user_index');

	$app->map('/edit(/:id)', function ($id = -1) use ($app)
	{
		if ( $id == 1 )
		{
			$Guard = new \App\Kernel\Back\Acl;
			if ( $Guard->isAdmin() == false ) $app->redirect( $app->config('forbidden.url') ) ;
		}
		
		$error    = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('user')
			->where(array('user_id' => $id))
			->find_one();

		if ( $id != -1 && !$contentRow ) {
			$app->redirect( $app->config('admin.url') . '/ext/user');
		}
		else {
			$post = $contentRow ;
		}
		
		if ( $app->request->isPost() ) {
			$post = array(
				"user_name" => $app->request->post('user_name'),
				"user_fname" => $app->request->post('user_fname'),
				"user_lname" => $app->request->post('user_lname'),
				"user_group_id" => $app->request->post('user_group_id')
			) ;
			
			if ( $app->request->post('user_name') == "" ) {
				$error = true ;
				$tabError['user_name'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			else {
				$exist = \DB::for_table('user')->where_equal('user_name' , $app->request->post('user_name'));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_id' , $id);
				$exist = $exist->count();
			}
			
			if ( $app->request->post('user_name') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_name'] = Translate::getInstance()->getText( 'already_use_login' );
			}
			
			if ( $app->request->post('user_fname') == "" ) {
				$error = true ;
				$tabError['user_fname'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( $app->request->post('user_lname') == "" ) {
				$error = true ;
				$tabError['user_lname'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( $id == -1 && $app->request->post('password') == "" && $app->request->post('confirm_password') != '' ) {
				$error = true ;
				$tabError['password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( $id == -1 && $app->request->post('confirm_password') == "" && $app->request->post('password') != '' ) {
				$error = true ;
				$tabError['confirm_password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( $id == -1 && $app->request->post('password') != $app->request->post('confirm_password') ) {
				$error = true ;
				$tabError['confirm_password'] = Translate::getInstance()->getText( 'msg_different_password' );
			}
			
			if ( $error == false ) {
				if ( !$contentRow ) {
					$contentRow = DB::for_table('user')->create();
					$contentRow->user_password = password_hash( $app->request->post('password') ,PASSWORD_BCRYPT,['cost' => 9]) ;
					$add = true ;
				}
				
				$contentRow->user_name 		= $app->request->post('user_name');
				$contentRow->user_fname 	= $app->request->post('user_fname');
				$contentRow->user_lname 	= $app->request->post('user_lname');
				$contentRow->user_group_id 	= $app->request->post('user_group_id');
				$contentRow->save();
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 4 : 5 ) , $contentRow->user_name ) ;
				
				$id = $contentRow->user_id;
				
				$app->flash('__msg',addslashes(  "L'utilisateur a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) );
				$app->flash('__result',true);
				
				if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/user/edit/' . $id );
				else 											$app->redirect( $app->config('admin.url') . '/ext/user' );
			}
		}
		
		$groupRows = \DB::for_table('user_group') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$groupRows = $groupRows->where_not_equal('user_group_id' , 1 ) ;
		}
		
		$groupRows = $groupRows->order_by_asc('user_group_name')
								   ->find_many();

		$app->render('ext/user/edit.twig.html', array(
												"contentRow" => $contentRow,
												"groupRows"  => $groupRows,
												"id" 		 => $id,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->name('user_edit')->via('GET', 'POST');

    $app->get('/delete/:id', function ($id) use ($app)
    {
        $app->render('delete.twig',[
            'id' => $id,
            'url' => \App\Kernel\Factory::getInstance()->Url()->get('ext/user/delete/' . $id )
        ]);
    });

    $app->post('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		if ( $id == $_SESSION[ $app->config('session') ]['id'] )
		{
			$msg = "Vous ne pouvez pas supprimer le compte avec lequel vous êtes actuellement connecté! $id - " . $_SESSION[ $app->config('session') ]['id'] ;
		}
		else
		{
			$contentRow = \DB::for_table('user')
				->where_equal('user_id' , $id)
				->where_not_equal('user_id' , $_SESSION[ $app->config('session') ]['id'] )
				->find_one();
			
			if ( $contentRow )
			{
				if ( $contentRow->user_group_id == 1 )
				{
					$msg = Translate::getInstance()->getText( 'delete_account_err' );
				}
				else
				{
					$log = \DB::for_table('log')
						->where_equal('log_user_id' , $id)
						->delete_many();
					
					\App\Kernel\Back\Log::getInstance()->warning( 3 , $contentRow->user_name ) ;
				
					$msg = Translate::getInstance()->getText('user_deleted' );
					$ret = true ;
					$contentRow->delete();
				}
			}
			else
			{
				$msg = Translate::getInstance()->getText('delete_error' );
			}

		}
		
		echo json_encode([
            "msg" => $msg ,
            "result" => $ret,
            'url' => \App\Kernel\Factory::getInstance()->Url()->get('ext/user')
        ]) ;
	})->name('user_delete');
});