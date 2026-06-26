<?php

use App\Kernel\Config;
use App\Kernel\AppContext;
use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/page', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
		$contentRows = \DB::for_table('page')
								->order_by_asc('page_name')
								->find_many();
        $content = [];

        if ( $contentRows ) $content[0] = [ 'id' => 0 , 'page' => [] ];

        $reqDomains = \DB::for_table('domain')
            ->select('domain_id')
            ->select('domain_name')
            ->find_many();

        if( $reqDomains )
        {
            foreach( $reqDomains as $row )
            {
                $content[ $row->domain_id ] = [
                    'id'   => $row->domain_id,
                    'name' => $row->domain_name,
                    'page' => []
                ];
            }
        }

        foreach( $contentRows as $row )
        {
            if( !empty($content) && in_array( $row->page_domain_id , array_keys($content) ) )
            {
                $content[ $row->page_domain_id ]['page'][] = $row;
            }
            else
            {
                $content[0]['page'][] = $row;
            }
        }

		return \App\Kernel\AppContext::twig()->render($res, 'ext/page/index.twig.html', array( "content" => $content ));
	})->setName('page_index');

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

				$msg = Translate::getInstance()->getText( 'msg_page_suppr' );
				$ret = true ;
				$contentRow->delete();
			}
			else
			{
				$msg = Translate::getInstance()->getText( 'delete_page_err' );
			}
		}
		else
		{
			$msg = Translate::getInstance()->getText( 'delete_error' );
		}
		$Factory = \App\Kernel\Factory::getInstance() ;
		$Factory->Response()->returnJSON( $msg , $ret ) ;
	})->setName('page_delete');

    $app->get('/active/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ Config::getInstance()->get('token') ] ) {
            $page = \DB::for_table('page')
                ->where_equal('page_id' , $id)
                ->find_one();

            if ( $page->page_active == 0 ) {
                $page->page_active = 1;
                $page->save();

                \App\Kernel\Back\Log::getInstance()->warning( 30 , $page->page_name ) ;

                $msg = Translate::getInstance()->getText( 'page_activated' );
                $ret = true;
            }
            else {
                $msg = Translate::getInstance()->getText( 'page_already_activated_err') ;
                $ret = false;
            }
        }
        else {
            $msg = Translate::getInstance()->getText( 'msg_token_invalide' );
            $ret = false;
        }

        $Factory = \App\Kernel\Factory::getInstance() ;
        $Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
    })->setName('page_active');

    $app->get('/index/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ Config::getInstance()->get('token') ] ) {
            $page = \DB::for_table('page')
                ->where_equal('page_id' , $id)
                ->find_one();

            if ( $page->page_index == 0 ) {
                $page->page_index = 1;
                $page->save();

                \App\Kernel\Back\Log::getInstance()->warning( 54 , $page->page_name ) ;

                $msg = Translate::getInstance()->getText( 'msg_page_indexed' );
                $ret = true;
            }
            else {
                $msg = Translate::getInstance()->getText( 'indexed_page_err' );
                $ret = false;
            }
        }
        else {
            $msg = Translate::getInstance()->getText( 'msg_token_invalide' );
            $ret = false;
        }

        $Factory = \App\Kernel\Factory::getInstance() ;
        $Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
    })->setName('page_index');

    $app->get('/noindex/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ Config::getInstance()->get('token') ] ) {
            $page = \DB::for_table('page')
                ->where_equal('page_id' , $id)
                ->find_one();

            if ( $page->page_index == 1 ) {
                $page->page_index = 0;
                $page->save();

                \App\Kernel\Back\Log::getInstance()->warning( 55 , $page->page_name ) ;

                $msg = Translate::getInstance()->getText( 'page_deindexed_err' );
                $ret = true;
            }
            else {
                $msg = Translate::getInstance()->getText( 'page_already_deindexed_err' );
                $ret = false;
            }
        }
        else {
            $msg = Translate::getInstance()->getText( 'msg_token_invalide' );
            $ret = false;
        }

        $Factory = \App\Kernel\Factory::getInstance() ;
        $Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
    })->setName('page_index');

	$app->get('/default/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ Config::getInstance()->get('token') ] ) {
            $page = \DB::for_table('page')
							->where_equal('page_id' , $id)
							->find_one();

			if ( ! $page )
			{
				Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/page' );
			}

			$pageDefault = \DB::for_table('page')
							->where_equal('page_default' , 1)
							->where_equal('page_domain_id' , $page->page_domain_id)
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

			$msg = Translate::getInstance()->getText( 'default_page_modified' );
			$ret = true;
		}
		else {
			$msg = Translate::getInstance()->getText( 'msg_token_invalide' );
			$ret = false;
		}

		$Factory = \App\Kernel\Factory::getInstance() ;
		$Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
	})->setName('page_active');

	$app->get('/disactive/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ Config::getInstance()->get('token') ] ) {
			$page = \DB::for_table('page')
							->where_equal('page_id' , $id)
							->find_one();

			if ( ! $page )
			{
				Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/page' );
			}

			if ( $page->page_active == 1 )
			{
				if ( $page->page_default == 0 )
				{
					$page->page_active = 0;
					$page->save();

					\App\Kernel\Back\Log::getInstance()->warning( 31 , $page->page_name ) ;

					$msg = Translate::getInstance()->getText( 'page_desactivated' );
					$ret = true;
				}
				else
				{
					$msg = Translate::getInstance()->getText( 'default_page_err' );
					$ret = false;
				}
			}
			else
			{
				$msg = Translate::getInstance()->getText( 'page_already_desactivated_err' );
				$ret = false;
			}
		}
		else {
			$msg =  Translate::getInstance()->getText( 'msg_token_invalide' );
			$ret = false;
		}

		$Factory = \App\Kernel\Factory::getInstance() ;
		$Factory->Response()->flashAndRedirect( $msg , $ret , 'ext/page' ) ;
	})->setName('page_disactive');

	$app->get('/edit/:id', function ($id = -1) use ($app)
	{
		$lang 	  = \App\Kernel\Lang::getInstance()->getAll() ;
		$error 	  = false ;
		$tabError = array() ;

		$contentRow = \DB::for_table('page')
			->where_equal('page_id' , $id)
			->find_one();

		if ( $id != -1 && !$contentRow )
		{
			Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/page');
		}

		$post = $contentRow ;

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
			}
		}

		return \App\Kernel\AppContext::twig()->render($res, 'ext/page/edit.twig.html', array(
			"post" => $post,
            'priority' =>  $contentRow->page_priority,
			"id" => $id,
			"lang" => $lang,
			"contentLang" => $contentLang,
			"error"		 => ( $error === false ? "0" : "1" ),
			"tabError"	 => json_encode( $tabError )
		));
	})->setName('page_edit');

	$app->post('/edit/:id', function($id = -1) use ($app)
	{
		$post = array(
			"page_name"   => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : ''),
			"page_active" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_active'] ?? '') : '')
		) ;

		$contentRow = \DB::for_table('page')
						 ->where_equal('page_id' , $id)
						 ->find_one();

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

		if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '') == "" )
		{
			$error = true ;
			$tabError['page_name'] = Translate::getInstance()->getText( 'mandatory_fillin' );
		}

		if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_priority'] ?? '') : '') == "" )
		{
			$error = true ;
			$tabError['page_priority'] = Translate::getInstance()->getText( 'mandatory_priority' );
		}

		if ( $error == false )
		{
			$lang 	  = \App\Kernel\Lang::getInstance()->getAll() ;

			$contentRow->page_name 			= (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '') ;
			$contentRow->page_priority 		= (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_priority'] ?? '') : '') ;
			$contentRow->page_active 		= ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_active'] ?? '') : '') == NULL ? 0 : 1 ) ;
			$contentRow->page_index 		=  (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_index'] ?? '') : '');

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

				$lasturl = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['last_page_lang_url_' . $l->url] ?? '') : '') ;
				$url = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_lang_url_' . $l->url] ?? '') : '') ;
				if ( $url == '' ) $url = $contentRow->page_name ;
				if ( $url != $lasturl ) $url = $Factory->Url()->uniq( $url , $l->id ) ;

				$cLang->page_lang_page_id 		= $id ;
				$cLang->page_lang_lang_id 		= $l->id ;
				$cLang->page_lang_url 			= $url ;
				$cLang->page_lang_title 		= ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_lang_title_' . $l->url] ?? '') : '') == '' ? (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '') : (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_lang_title_' . $l->url] ?? '') : '') ) ;
				$cLang->page_lang_title 		= ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_lang_title_' . $l->url] ?? '') : '') == '' ? (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '') : (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_lang_title_' . $l->url] ?? '') : '') ) ;
				$cLang->page_lang_description 	= ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_lang_description_' . $l->url] ?? '') : '') == '' ? NULL : (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_lang_description_' . $l->url] ?? '') : '') ) ;
                $cLang->save();
			}

			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['buttonaction'] ?? '') : '') == "stay" ) 	$url = '/ext/page/edit/' . $id ;
			else 										         	$url = '/ext/page' ;

			$result = [];
			$result['result'] = true ;
			$result['url']   = Factory::getInstance()->Url()->get( $url ) ;
			$result['msg']   = "La page spéciale a bien été " . ( $add == true ? "ajouté" : "modifié" ) ;
			Factory::getInstance()->Response()->printJSON($result) ;
		}
		else
		{
			$result['result'] = false ;
			$result['msg']    = current( $tabError );
			Factory::getInstance()->Response()->printJSON($result) ;
		}

	})->setName('page_edit');
});