<?php

$app->group('/user_front', function () use ($app)
{
	$app->get('/', function () use ($app)
    {
		$contentRows = \DB::for_table('user_front')
            ->join( 'user_front_profile', ['user_front_profile_user_front_id', '=', 'user_front_id'] )
            ->order_by_asc('user_front_id')
			->find_many();

        $app->render('ext/user_front/index.twig.html', [
            "contentRows" => $contentRows
        ]);

	})->name('user_front_index');

	$app->map('/edit(/:id)', function ($id = -1) use ($app)
	{
		if ( $id == 1 )
		{
			$Guard = new \App\Kernel\Back\Acl;
			if ( $Guard->isAdmin() == false ) $app->redirect( $app->config('forbidden.url') ) ;
		}
		
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
		
		if ( $app->request->isPost() )
		{
			$post = [
				"user_front_login" => $app->request->post('user_front_login'),
				"user_front_group_id" => $app->request->post('user_front_group_id')
            ] ;
			
			if ( $app->request->post('user_front_login') == "" ) {
				$error = true ;
				$tabError['user_front_login'] = "Veuillez remplir ce champ" ;
			}
            elseif ( !filter_var( $app->request->post('user_front_login') , FILTER_VALIDATE_EMAIL ) ) {
                $error = true ;
                $tabError['user_front_login'] = "Veuillez saisir une adresse e-mail" ;
            }
			else {
				$exist = \DB::for_table('user_front')->where_equal('user_front_login' , $app->request->post('user_front_login'));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_front_id' , $id);
				$exist = $exist->count();
			}
			
			if ( $app->request->post('user_front_login') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_front_login'] = "Ce login est deja utilisé" ;
			}

			
			if ( $id == -1 && $app->request->post('password') == "" && $app->request->post('confirm_password') != '' ) {
				$error = true ;
				$tabError['password'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $id == -1 && $app->request->post('confirm_password') == "" && $app->request->post('password') != '' ) {
				$error = true ;
				$tabError['confirm_password'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $id == -1 && $app->request->post('password') != $app->request->post('confirm_password') ) {
				$error = true ;
				$tabError['confirm_password'] = "Les 2 mots de passe sont différents" ;
			}
			
			if ( $error == false ) {
				if ( !$contentRow ) {
					$contentRow = DB::for_table('user_front')->create();
					$contentRow->user_front_password = password_hash( $app->request->post('password') ,PASSWORD_BCRYPT,['cost' => 9]) ;

                    $contentProfile = DB::for_table('user_front_profile')->create();

                    $add = true ;
                }

                $contentRow->user_front_login 		            = $app->request->post('user_front_login');
                if ( $id == -1 ) $contentRow->user_front_token  = \App\Kernel\Back\User::getInstance()->getNewToken();
                $contentRow->user_front_user_front_group_id 	= $app->request->post('user_front_group_id');
                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 50 : 49 ) , $contentRow->user_front_name ) ;

                $id = $contentRow->user_front_id;

                $contentProfile->user_front_profile_user_front_id = $id;
                $contentProfile->save();
				
				$app->flash('__msg',addslashes( json_encode( "L'utilisateur a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
				$app->flash('__result',true);
				
				if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/user_front/edit/' . $id );
				else 											$app->redirect( $app->config('admin.url') . '/ext/user_front' );
			}
		}
		
		$groupRows = \DB::for_table('user_front_group') ;
		
		if ( $app->environment['user']['group_id'] != 1 ) {
			$groupRows = $groupRows->where_not_equal('user_front_user_front_group_id' , 1 ) ;
		}
		
		$groupRows = $groupRows->order_by_asc('user_front_group_name')
								   ->find_many();

		$app->render('ext/user_front/edit.twig.html', array(
												"contentRow" => $contentRow,
												"groupRows"  => $groupRows,
												"id" 		 => $id,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->name('user_edit')->via('GET', 'POST');

	$app->delete('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		if ( $id == $_SESSION[ $app->config('session') ]['id'] )
		{
			$msg = "Vous ne pouvez pas supprimer le compte avec lequel vous êtes actuellement connecté!" ;
		}
		else
		{
			$contentRow = \DB::for_table('user_front')
				->where_equal('user_front_id' , $id)
				->where_not_equal('user_front_id' , $_SESSION[ $app->config('session') ]['id'] )
				->find_one();
			
			if ( $contentRow )
			{
			    $contentProfile = \DB::for_table('user_front_profile')
                    ->where_equal( 'user_front_profile_user_front_id', $contentRow->user_front_id )
                    ->find_one();

                \App\Kernel\Back\Log::getInstance()->warning( 48 , $contentRow->user_front_name ) ;

                $msg = "L'utilisateur a bien été supprimé" ;
                $ret = true ;
                $contentRow->delete();
			}
			else
			{
				$msg = "Une erreur est survenue lors de la suppression" ;
			}

		}
		
		echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
	})->name('user_delete');
});