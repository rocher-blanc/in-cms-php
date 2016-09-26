<?php

$app->group('/page', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
		$contentRows = \DB::for_table('page')
								->order_by_asc('page_name')
								->find_many();
		
		$app->render('ext/page/index.twig.html', array( "contentRows" => $contentRows ));

	})->name('page_index');

	$app->delete('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		$contentRow = \DB::for_table('page')
			->where_equal('page_id' , $id)
			->find_one();
		
		if ( $contentRow )
		{
			if ( $contentRow->page_default == 0 )
			{
				\App\Kernel\Back\Log::getInstance()->warning( 32 , $contentRow->page_name ) ;
			
				$msg = "La page a bien été supprimé" ;
				$ret = true ;
				$cLang = \DB::for_table('page_lang')->where(['page_lang_page_id' => $id])->delete_many();
				$contentRow->delete();
			}
			else
			{
				$msg = "Impossible de supprimer la page par défaut, il faut en définir une nouvelle avant." ;
			}
		}
		else
		{
			$msg = "Une erreur est survenue lors de la suppression" ;
		}
		$Factory = \App\Kernel\Factory::getInstance() ;
		$Factory->Response()->returnJSON( $msg , $ret ) ;
	})->name('page_delete');
	
	$app->get('/active/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ $app->config('token') ] ) {
			$page = \DB::for_table('page')
							->where_equal('page_id' , $id)
							->find_one();
			
			if ( $page->page_active == 0 ) {
				$page->page_active = 1;
				$page->save();
				
				\App\Kernel\Back\Log::getInstance()->warning( 30 , $page->page_name ) ;
						
				$msg = "La page a bien été activé";
				$ret = true;
			}
			else {
				$msg = "Impossible, la page est déja activée";
				$ret = false;
			}
		}
		else {
			$msg = "Le token de sécurité est invalide";
			$ret = false;
		}
		
		$Factory = \App\Kernel\Factory::getInstance() ;
		$Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
	})->name('page_active');
	
	$app->get('/default/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ $app->config('token') ] ) {
			$page = \DB::for_table('page')
							->where_equal('page_id' , $id)
							->find_one();
			
			if ( ! $page )
			{
				$app->redirect( $app->config('admin.url') . '/ext/page' );
			}
			
			$pageDefault = \DB::for_table('page')
							->where_equal('page_default' , 1)
							->find_one();
			if ( $pageDefault )
			{
				$pageDefault->page_default = 0;
				$pageDefault->save();
			}
			
			$page->page_default = 1;
			$page->page_active = 1;
			$page->save();
			
			\App\Kernel\Back\Log::getInstance()->warning( 34 , $page->page_name ) ;
					
			$msg = "La page par défaut a bien été modifié";
			$ret = true;
		}
		else {
			$msg = "Le token de sécurité est invalide";
			$ret = false;
		}
		
		$Factory = \App\Kernel\Factory::getInstance() ;
		$Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
	})->name('page_active');
	
	$app->get('/disactive/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ $app->config('token') ] ) {
			$page = \DB::for_table('page')
							->where_equal('page_id' , $id)
							->find_one();
			
			if ( ! $page )
			{
				$app->redirect( $app->config('admin.url') . '/ext/page' );
			}
			
			if ( $page->page_active == 1 )
			{
				if ( $page->page_default == 0 )
				{
					$page->page_active = 0;
					$page->save();
					
					\App\Kernel\Back\Log::getInstance()->warning( 31 , $page->page_name ) ;
						
					$msg = "La page a bien été désactivé";
					$ret = true;
				}
				else
				{
					$msg = "Impossible, la page par défaut doit toujours être activée";
					$ret = false;
				}
			}
			else
			{
				$msg = "Impossible, la page est déja désactivée";
				$ret = false;
			}
		}
		else {
			$msg = "Le token de sécurité est invalide";
			$ret = false;
		}
		
		$Factory = \App\Kernel\Factory::getInstance() ;
		$Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
	})->name('page_disactive');
	
	$app->map('/edit/:id', function ($id = -1) use ($app)
	{
		$lang 	  = \App\Kernel\Lang::getInstance()->getAll() ;
		$error 	  = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('page')
			->where_equal('page_id' , $id)
			->find_one();
		
		if ( $id != -1 && !$contentRow )
		{
			$app->redirect( $app->config('admin.url') . '/ext/page');
		}
		
		if ( $app->request->isPost() )
		{
			$post = array(
				"page_name" => $app->request->post('page_name'),
				"page_active" => $app->request->post('page_active')
			) ;
			
			if ( !$contentRow )
			{
				$contentRow = \DB::for_table('page')->create();
				$add = true ;
				
				$ct = \DB::for_table('page')->count();
				
				if ( $ct == 0 )
				{
					$contentRow->page_default = 1;
					$forceActive = true ;
				}
			}
			
			if ( $app->request->post('page_name') == "" )
			{
					$error = true ;
					$tabError['page_name'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $app->request->post('page_priority') == "" )
			{
					$error = true ;
					$tabError['page_priority'] = "Veuillez indiquer une priorité" ;
			}
			
			if ( $error == false )
			{
				$contentRow->page_name 			= $app->request->post('page_name') ;
				$contentRow->page_priority 		= $app->request->post('page_priority') ;
				$contentRow->page_active 		= ( $app->request->post('page_active') == NULL ? 0 : 1 ) ;
				
				if ( $forceActive == true ) $contentRow->page_active = 1;
				
				$contentRow->save() ;
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 33 : 29 ) , $contentRow->page_name ) ;
				
				$id = $contentRow->page_id;
				
				foreach( $lang as $l )
				{
					$cLang = \DB::for_table('page_lang')->where(['page_lang_page_id' => $id, 'page_lang_lang_id' => $l->id])->find_one();
					
					if ( ! $cLang )
					{
						$cLang = \DB::for_table('page_lang')->create();
					}
					
					$Factory = \App\Kernel\Factory::getInstance() ;

                    $lasturl = $app->request->post('last_page_lang_url_' . $l->url ) ;
                    $url = $app->request->post('page_lang_url_' . $l->url ) ;
                    if ( $url == '' ) $url = $contentRow->page_name ;
                    if ( $url != $lasturl ) $url = $Factory->Url()->uniq( $url , $l->id ) ;
					
					$cLang->page_lang_page_id 		= $id ;
					$cLang->page_lang_lang_id 		= $l->id ;
					$cLang->page_lang_url 			= $url ;
					$cLang->page_lang_title 		= ( $app->request->post('page_lang_title_' . $l->url ) == '' ? NULL : $app->request->post('page_lang_title_' . $l->url ) ) ;
					$cLang->page_lang_description 	= ( $app->request->post('page_lang_description_' . $l->url ) == '' ? NULL : $app->request->post('page_lang_description_' . $l->url ) ) ;
					$cLang->page_lang_keyword 		= ( $app->request->post('page_lang_keyword_' . $l->url ) == '' ? NULL : $app->request->post('page_lang_keyword_' . $l->url ) ) ;
					$cLang->save();
				}
				
				if ( $app->request->post('submit') == "stay" ) 	$url = '/ext/page/edit/' . $id ;
				else 											$url = '/ext/page' ;
				
				$Factory->Response()->flashAndRedirect("La page spéciale a bien été " . ( $add == true ? "ajoutée" : "modifiée" ) , true , $url ) ;
			}
		}
		else
		{
			$post = $contentRow ;
		}
		
		$postLang = \DB::for_table('page_lang')
			->where(['page_lang_page_id' => $id])
			->find_many();
		
		$contentLang = [] ;
		if ( $postLang )
		{
			foreach( $postLang as $row )
			{
				$contentLang[ $row->page_lang_lang_id ]['page_lang_url'] 		 = $row->page_lang_url ;
				$contentLang[ $row->page_lang_lang_id ]['page_lang_title'] 		 = $row->page_lang_title ;
				$contentLang[ $row->page_lang_lang_id ]['page_lang_description'] = $row->page_lang_description ;
				$contentLang[ $row->page_lang_lang_id ]['page_lang_keyword'] 	 = $row->page_lang_keyword ;
			}
		}
		
		$app->render('ext/page/edit.twig.html', array( 
			"post" => $post,
            'priority' =>  $contentRow->page_priority,
			"id" => $id,
			"arrayController" => $arrayController,
			"lang" => $lang,
			"contentLang" => $contentLang,
			"error"		 => ( $error === false ? "0" : "1" ),
			"tabError"	 => json_encode( $tabError )
		));
	})->name('page_edit')->via('GET', 'POST');
});