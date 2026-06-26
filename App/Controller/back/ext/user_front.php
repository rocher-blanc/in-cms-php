<?php

use App\Kernel\Front\Translate;

$app->group('/user_front', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
    {
		$contentRows = \DB::for_table('user_front')
            ->join( 'user_front_profile', ['user_front_profile_user_front_id', '=', 'user_front_id'] )
            ->order_by_asc('user_front_id')
			->find_many();

        return \App\Kernel\AppContext::twig()->render($res, 'ext/user_front/index.twig.html', [
            "contentRows" => $contentRows
        ]);

	})->name('user_front_index');

	$app->map(['GET', 'POST'], '/edit[/{id}]', function ($id = -1) use ($app)
	{
		$error    = false ;
		$tabError = [] ;
		
		$contentRow = \DB::for_table('user_front')
			->where(array('user_front_id' => $id))
			->find_one();

        if( $contentRow !== false )
        {
            $contentProfile = \DB::for_table('user_front_profile')
                ->where_equal( 'user_front_profile_user_front_id', $contentRow->get('user_front_id') )
                ->find_one();
        }

		if ( $id != -1 && !$contentRow )
		{
			$app->redirect( $app->config('admin.url') . '/ext/user_front');
		}
		else
		{
			$post = $contentRow ;
		}
		
		if ( strtoupper($req->getMethod()) === 'POST' )
		{
			$post = [
				"user_front_login" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_login'] ?? '') : ''),
				"user_front_group_id" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_group_id'] ?? '') : '')
            ] ;
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_login'] ?? '') : '') == "" ) {
				$error = true ;
				$tabError['user_front_login'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
            elseif ( !filter_var( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_login'] ?? '') : '') , FILTER_VALIDATE_EMAIL ) ) {
                $error = true ;
                $tabError['user_front_login'] = Translate::getInstance()->getText( 'mandatory_email_address' );
            }
			else {
				$exist = \DB::for_table('user_front')->where_equal('user_front_login' , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_login'] ?? '') : ''));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_front_id' , $id);
				$exist = $exist->count();
			}
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_login'] ?? '') : '') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_front_login'] = Translate::getInstance()->getText( 'already_use_login' );
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
					$contentRow = DB::for_table('user_front')->create();
					$contentRow->user_front_password = password_hash( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['password'] ?? '') : '') ,PASSWORD_BCRYPT,['cost' => 9]) ;

                    $contentProfile = DB::for_table('user_front_profile')->create();

                    $add = true ;
                }

                $contentRow->user_front_login 		            = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_login'] ?? '') : '');
                if ( $id == -1 ) $contentRow->user_front_token  = \App\Kernel\Back\User::getInstance()->getNewToken();
                $contentRow->user_front_user_front_group_id 	= (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_group_id'] ?? '') : '');
                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 50 : 49 ) , $contentRow->user_front_name ) ;

                $id = $contentRow->user_front_id;

                $contentProfile->user_front_profile_user_front_id = $id;
                $contentProfile->save();
				
				$app->flash('__msg',addslashes( json_encode( "L'utilisateur a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
				$app->flash('__result',true);
				
				if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['submit'] ?? '') : '') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/user_front/edit/' . $id );
				else 											$app->redirect( $app->config('admin.url') . '/ext/user_front' );
			}
		}
		
		$groupRows = \DB::for_table('user_front_group') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$groupRows = $groupRows->where_not_equal('user_front_user_front_group_id' , 1 ) ;
		}
		
		$groupRows = $groupRows->order_by_asc('user_front_group_name')
								   ->find_many();

		return \App\Kernel\AppContext::twig()->render($res, 'ext/user_front/edit.twig.html', array(
												"contentRow" => $contentRow,
												"groupRows"  => $groupRows,
												"id" 		 => $id,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->name('user_edit');

	$app->delete('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
        $contentRow = \DB::for_table('user_front')
            ->where_equal('user_front_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            $contentProfile = \DB::for_table('user_front_profile')
                ->where_equal( 'user_front_profile_user_front_id', $contentRow->user_front_id )
                ->find_one();

            \App\Kernel\Back\Log::getInstance()->warning( 48 , $contentRow->user_front_name ) ;

            $msg = s ;
            $ret = true ;
            $contentRow->delete();
        }
        else
        {
            $msg = Translate::getInstance()->getText( 'delete_error' );
        }
		
		echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
	})->name('user_delete');
});