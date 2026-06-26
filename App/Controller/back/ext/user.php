<?php

use App\Kernel\Config;
use App\Kernel\AppContext;
use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/user', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {

		$contentRows = \DB::for_table('user') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$contentRows = $contentRows->where_not_equal('user_group_id' , 1 ) ;
		}
		
		$contentRows = $contentRows->order_by_asc('user_id')
								   ->find_many();
		
		return \App\Kernel\AppContext::twig()->render($res, 'ext/user/index.twig.html', array( "contentRows" => $contentRows ));

	})->setName('user_index');

	$app->map(['GET', 'POST'], '/edit[/{id}]', function ($id = -1) use ($app)
	{
		if ( $id == 1 )
		{
			$Guard = new \App\Kernel\Back\Acl;
			if ( $Guard->isAdmin() == false ) Factory::getInstance()->Response()->redirect( Config::getInstance()->get('forbidden.url') ) ;
		}
		
		$error    = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('user')
			->where(array('user_id' => $id))
			->find_one();

		if ( $id != -1 && !$contentRow ) {
			Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/user');
		}
		else {
			$post = $contentRow ;
		}
		
		if ( strtoupper($req->getMethod()) === 'POST' ) {
			$post = array(
				"user_name" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_name'] ?? '') : ''),
				"user_fname" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_fname'] ?? '') : ''),
				"user_lname" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_lname'] ?? '') : ''),
				"user_group_id" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_group_id'] ?? '') : '')
			) ;
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_name'] ?? '') : '') == "" ) {
				$error = true ;
				$tabError['user_name'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			else {
				$exist = \DB::for_table('user')->where_equal('user_name' , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_name'] ?? '') : ''));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_id' , $id);
				$exist = $exist->count();
			}
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_name'] ?? '') : '') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_name'] = Translate::getInstance()->getText( 'already_use_login' );
			}
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_fname'] ?? '') : '') == "" ) {
				$error = true ;
				$tabError['user_fname'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_lname'] ?? '') : '') == "" ) {
				$error = true ;
				$tabError['user_lname'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( $id == -1 && (is_array($req->getParsedBody()) ? ($req->getParsedBody()['password'] ?? '') : '') == "" && (is_array($req->getParsedBody()) ? ($req->getParsedBody()['confirm_password'] ?? '') : '') != '' ) {
				$error = true ;
				$tabError['password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( $id == -1 && (is_array($req->getParsedBody()) ? ($req->getParsedBody()['confirm_password'] ?? '') : '') == "" && (is_array($req->getParsedBody()) ? ($req->getParsedBody()['password'] ?? '') : '') != '' ) {
				$error = true ;
				$tabError['confirm_password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			
			if ( $id == -1 && (is_array($req->getParsedBody()) ? ($req->getParsedBody()['password'] ?? '') : '') != (is_array($req->getParsedBody()) ? ($req->getParsedBody()['confirm_password'] ?? '') : '') ) {
				$error = true ;
				$tabError['confirm_password'] = Translate::getInstance()->getText( 'msg_different_password' );
			}
			
			if ( $error == false ) {
				if ( !$contentRow ) {
					$contentRow = DB::for_table('user')->create();
					$contentRow->user_password = password_hash( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['password'] ?? '') : '') ,PASSWORD_BCRYPT,['cost' => 9]) ;
					$add = true ;
				}
				
				$contentRow->user_name 		= (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_name'] ?? '') : '');
				$contentRow->user_fname 	= (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_fname'] ?? '') : '');
				$contentRow->user_lname 	= (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_lname'] ?? '') : '');
				$contentRow->user_group_id 	= (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_group_id'] ?? '') : '');
				$contentRow->save();
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 4 : 5 ) , $contentRow->user_name ) ;
				
				$id = $contentRow->user_id;
				
				AppContext::flash()->addMessage('__msg',addslashes(  "L'utilisateur a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) );
				AppContext::flash()->addMessage('__result',true);
				
				if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['submit'] ?? '') : '') == "stay" ) 	Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/user/edit/' . $id );
				else 											Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/user' );
			}
		}
		
		$groupRows = \DB::for_table('user_group') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$groupRows = $groupRows->where_not_equal('user_group_id' , 1 ) ;
		}
		
		$groupRows = $groupRows->order_by_asc('user_group_name')
								   ->find_many();

		return \App\Kernel\AppContext::twig()->render($res, 'ext/user/edit.twig.html', array(
												"contentRow" => $contentRow,
												"groupRows"  => $groupRows,
												"id" 		 => $id,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->setName('user_edit');

    $app->get('/delete/:id', function ($id) use ($app)
    {
        return \App\Kernel\AppContext::twig()->render($res, 'delete.twig', [
            'id' => $id,
            'url' => \App\Kernel\Factory::getInstance()->Url()->get('ext/user/delete/' . $id )
        ]);
    });

    $app->post('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		if ( $id == $_SESSION[ Config::getInstance()->get('session') ]['id'] )
		{
			$msg = "Vous ne pouvez pas supprimer le compte avec lequel vous êtes actuellement connecté! $id - " . $_SESSION[ Config::getInstance()->get('session') ]['id'] ;
		}
		else
		{
			$contentRow = \DB::for_table('user')
				->where_equal('user_id' , $id)
				->where_not_equal('user_id' , $_SESSION[ Config::getInstance()->get('session') ]['id'] )
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
	})->setName('user_delete');
});