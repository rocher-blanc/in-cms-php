<?php

$app->group('/user_front_group', function () use ($app)
{
	$app->get('/', function () use ($app) {

		$contentRows = \DB::for_table('user_front_group')
                            ->order_by_asc('user_front_group_name')
                            ->find_many();
		
		$app->render('ext/user_front_group/index.twig.html', array( "contentRows" => $contentRows ));

	})->name('user_front_index');

	$app->map('/edit(/:id)', function ($id = -1) use ($app)
	{
		if ( $id == 1 )
		{
			$Guard = new \App\Kernel\Back\Acl;
			if ( $Guard->isAdmin() == false ) $app->redirect( $app->config('forbidden.url') ) ;
		}
		
		$error    = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('user_front_group')
			->where(array('user_front_group_id' => $id))
			->find_one();

		if ( $id != -1 && !$contentRow ) {
			$app->redirect( $app->config('admin.url') . '/ext/user_front_group');
		}
		else {
			$post = $contentRow ;
		}
		
		if ( $app->request->isPost() ) {
			$post = array(
				"user_front_group_name" => $app->request->post('user_front_group_name')
			) ;
			
			if ( $app->request->post('user_front_group_name') == "" ) {
				$error = true ;
				$tabError['user_front_group_name'] = "Veuillez remplir ce champ" ;
			}
			else {
				$exist = \DB::for_table('user_front_group')->where_equal('user_front_group_name' , $app->request->post('user_front_group_name'));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_front_group_id' , $id);
				$exist = $exist->count();
			}
			
			if ( $app->request->post('user_front_group_name') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_front_group_name'] = "Ce nom de groupe est deja utilisé" ;
			}
			
			if ( $error == false ) {
				if ( !$contentRow ) {
					$contentRow = DB::for_table('user_front_group')->create();
					$add = true ;
				}
				
				$contentRow->user_front_group_name = $app->request->post('user_front_group_name');
				$contentRow->save();
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 7 : 8 ) , $contentRow->user_front_group_name ) ;
				
				$id = $contentRow->user_front_group_id ;
				
				$app->flash('__msg',addslashes( json_encode( "Le groupe a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
				$app->flash('__result',true);
				
				if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/user_front_group/edit/' . $id );
				else 											$app->redirect( $app->config('admin.url') . '/ext/user_front_group' );
			}
		}

		$app->render('ext/user_front_group/edit.twig.html', array(
												"contentRow" => $contentRow,
												"id" 		 => $id,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->name('user_front_group_edit')->via('GET', 'POST');

	$app->delete('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
        $contentRow = \DB::for_table('user_front_group')
            ->where_equal('user_front_group_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 6 , $contentRow->user_front_group_name ) ;

            $msg = "Le groupe a bien été supprimé" ;
            $ret = true ;
            $contentRow->delete();
        }
        else
        {
            $msg = "Une erreur est survenue lors de la suppression" ;
        }
		
		echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
	})->name('user_front_group_delete');
});