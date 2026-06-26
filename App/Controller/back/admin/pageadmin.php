<?php

use App\Kernel\Front\Translate;
use App\Kernel\Factory;

$app->group('/pageadmin', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
        $domain = false ;
        $tab = [];

        $reqDomains = \DB::for_table('domain')
            ->select('domain_id')
            ->select('domain_name')
            ->find_many();

        if ( $reqDomains )
        {
            // il y a une gestion de domaines
            $domain = true ;

            foreach( $reqDomains as $row )
            {
                $content[ $row->domain_id ] = [
                    'id'   => $row->domain_id,
                    'name' => $row->domain_name
                ];

                $content[ $row->domain_id ]['page'] = \DB::for_table('page')
                    ->where_equal('page_domain_id' , $row->domain_id)
                    ->order_by_asc('page_name')
                    ->find_many();

                $tab[ $row->domain_id ] = $row->domain_id;
            }

            $other = \DB::for_table('page')
                ->where_not_in('page_domain_id' , $tab)
                ->order_by_asc('page_name')
                ->find_many();

            if ( $other )
            {
                $content[0] = [
                    'id'   => 0,
                    'name' => "non classé",
                    'page' => $other
                ];
            }
        }
        else
        {
            // pas de gestion de domaines
            $content = \DB::for_table('page')
                ->order_by_asc('page_name')
                ->find_many();
        }
		
		return \App\Kernel\AppContext::twig()->render($res, 'admin/pageadmin/index.twig', [
            'domain' => $domain,
            'content' => $content
        ]);
	})->name('page_index');

	$app->get('/edit', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
		$error 	  = false ;
		$tabError = array() ;

		$post = array(
			"page_name" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : ''),
			"page_controller" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_controller'] ?? '') : ''),
			"page_active" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_active'] ?? '') : '')
		);

        $domains = [];
        $reqDomains = \DB::for_table('domain')
            ->select('domain_id')
            ->select('domain_name')
            ->find_many();

        if( $reqDomains )
        {
            foreach( $reqDomains as $row )
            {
                $domains[] = [
                    'id'   => $row->domain_id,
                    'name' => $row->domain_name,
                    'selected' => false
                ];
            }
        }

        $groupRows = \DB::for_table('user_front_group')
            ->order_by_asc('user_front_group_name')
            ->find_many();

        return \App\Kernel\AppContext::twig()->render($res, 'admin/pageadmin/edit.twig', array(
            "post"       => $post,
            "id"         => -1,
            "domains"    => $domains,
            "groups"     => $groupRows,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError ),
    ));

	})->name('page_add');

	$app->post('/edit', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
	{
		$error 	  = false ;
		$tabError = array() ;

		$post = array(
			"page_name"       => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : ''),
			"page_controller" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_controller'] ?? '') : ''),
			"page_active"     => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_active'] ?? '') : '')
		);

        $domains = [];
        $reqDomains = \DB::for_table('domain')
            ->select('domain_id')
            ->select('domain_name')
            ->find_many();

        if( $reqDomains )
        {
            foreach( $reqDomains as $row )
            {
                $domains[] = [
                    'id'   => $row->domain_id,
                    'name' => $row->domain_name,
                    'selected' => false
                ];
            }
        }

		$contentRow = \DB::for_table('page')->create();

		$ct = \DB::for_table('page')
					->where_equal('page_domain_id' , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_domain_id'] ?? '') : ''))
					->count();

		if ($ct == 0)
		{
			$contentRow->page_default = 1;
			$forceActive = true;
		}

		if ((is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '') == "")
		{
			$error = true;
			$tabError['page_name'] = Translate::getInstance()->getText( 'mandatory_fillin');
		}

		if ($error == false)
		{
			$pageName = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '');

			$contentRow->page_name = $pageName;
			$contentRow->page_active = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_active'] ?? '') : '');
			$contentRow->page_index = 1;
			$contentRow->page_domain_id = ( $reqDomains ? (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_domain_id'] ?? '') : '') : 0 );

			if ( ACTIVE_USER )
			{
				$contentRow->page_access_user = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_access_user'] ?? '') : '');
				$contentRow->page_access_user_group = serialize( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_access_user_group'] ?? '') : '') );
			}
			$contentRow->save();

			\App\Kernel\Back\Log::getInstance()->info(33, $contentRow->page_name);

			$id     = $contentRow->page_id;
			$lang 	= \App\Kernel\Lang::getInstance()->getAll() ;

			foreach( $lang as $l )
			{
				$cLang = \DB::for_table('page_lang')->where(['page_lang_page_id' => $id, 'page_lang_lang_id' => $l->id])->find_one();

				if ( ! $cLang )
				{
					$cLang = \DB::for_table('page_lang')->create();
				}

				$Factory = \App\Kernel\Factory::getInstance() ;

				$url = $Factory->Url()->uniq( "page" . $id , $l->id ) ;

				$cLang->page_lang_page_id 		= $id ;
				$cLang->page_lang_lang_id 		= $l->id ;
				$cLang->page_lang_url 			= $url ;
				$cLang->page_lang_title 		= NULL ;
				$cLang->page_lang_description 	= NULL ;
				$cLang->save();
			}

			if ((is_array($req->getParsedBody()) ? ($req->getParsedBody()['submit'] ?? '') : '') == "stay") $url = '/admin/pageadmin/edit/' . $id;
			else                                         $url = '/admin/pageadmin';

			/* ************ Création du controller PHP ************ */
			$php = '' ;
			$php.= "<"."?"."php\n\n" ;
			$php.= "namespace Project\Controller\Front;\n\n" ;
			$php.= "/* Page $id : $pageName */\n" ;
			$php.= "class Page" . $id . " extends \App\Kernel\Front\Page\n" ;
			$php.= "{\n" ;
			$php.= "\t\n" ;
			$php.= "}" ;
			if ( ! file_exists( PROJECT_PATH . "/Controller/Front/Page" . $id . ".php" ) ) \App\Kernel\Factory::getInstance()->File()->create( PROJECT_PATH . "/Controller/Front/Page" . $id . ".php" , $php );

			/* ************ Création du template Twig ************ */
			$tpl = "{# Page $id : $pageName #}\n";
			$tpl.= '{% extends \'base.twig\' %}' . "\n";
			$tpl.= '{% block contenu %}Page ' . $id . '{% endblock %}' . "\n";
			if ( ! file_exists( PROJECT_PATH . "/view/front/page/page-" . $id . ".twig" ) ) \App\Kernel\Factory::getInstance()->File()->create( PROJECT_PATH . "/view/front/page/page-" . $id . ".twig" , $tpl );


			/* ************ Création de base.twig.html s'il n'existe pas ************ */
			$tpl = "{% extends 'layout.twig.html' %}\n" ;
			$tpl.= '{% block content %}' . "\n";
			$tpl.= "\t{{ block('contenu') }}\n" ;
			$tpl.= '{% endblock %}' . "\n";
			if ( ! file_exists( PROJECT_PATH . "/view/front/base.twig" ) ) \App\Kernel\Factory::getInstance()->File()->create( PROJECT_PATH . "/view/front/base.twig" , $tpl );

//			$Factory = \App\Kernel\Factory::getInstance();
//			$Factory->Response()->flashAndRedirect(Translate::getInstance()->getText( 'msg_special_page_add'), true, $url);
		}

        $groupRows = \DB::for_table('user_front_group')
            ->order_by_asc('user_front_group_name')
            ->find_many();

		$result = [];
		if( count($tabError) === 0 )
		{
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['buttonaction'] ?? '') : '') == "stay" ) 	$url = '/admin/pageadmin/edit/' . $id ;
			else 										         	$url = '/admin/pageadmin' ;

			$result['result'] = true ;
			$result['url']   = Factory::getInstance()->Url()->get( $url ) ;
			$result['msg']   = Translate::getInstance()->getText( 'msg_special_page_add');
		}
		else
		{
			$result['result'] = false ;
			$result['msg']    = current( $tabError );
		}
		Factory::getInstance()->Response()->printJSON($result) ;

	})->name('page_add');

    $app->get('/edit/:id', function ( $id ) use ($app)
    {
        $domains = [];
        $reqDomains = \DB::for_table('domain')
            ->select('domain_id')
            ->select('domain_name')
            ->find_many();
        if( $reqDomains )
        {
            foreach( $reqDomains as $row )
            {
                $domains[ $row->domain_id ] = [
                    'id'   => $row->domain_id,
                    'name' => $row->domain_name,
                    'selected' => false
                ];
            }
        }

        $contentRow = \DB::for_table('page')
            ->where_equal('page_id' , $id)
            ->find_one();

        $pageRedirect = \DB::for_table('page')
            ->select('page_id')
            ->select('page_name')
            ->where_equal('page_domain_id', $contentRow->page_domain_id)
            ->where_not_equal('page_id', $contentRow->page_id)
            ->find_many();

        $post = [
            "page_name" => $contentRow->page_name,
            "page_active" => $contentRow->page_active,
            "page_domain_id" => $contentRow->page_domain_id,
            "page_access_user" => $contentRow->page_access_user,
            "page_access_user_redirect" => $contentRow->page_access_user_redirect,
            "page_access_user_group" => unserialize( $contentRow->page_access_user_group )
        ] ;

        if( !empty($domains) && in_array( $post['page_domain_id'] , array_keys($domains) ) )
        {
            $domains[ $post['page_domain_id'] ]['selected'] = true;
        }

        $groupRows = \DB::for_table('user_front_group')
            ->order_by_asc('user_front_group_name')
            ->find_many();

        return \App\Kernel\AppContext::twig()->render($res, 'admin/pageadmin/edit.twig', array(
            "post"         => $post,
            "id"           => $id,
            "domains"      => $domains,
            "pageRedirect" => $pageRedirect,
            "groups"       => $groupRows
        ));

    });

    $app->post('/edit/:id', function ( $id ) use ($app)
    {
        $error 	  = false ;
        $tabError = array() ;

        $contentRow = \DB::for_table('page')
            ->where_equal('page_id' , $id)
            ->find_one();

        if ( strtoupper($req->getMethod()) === 'POST' )
        {
            $post = array(
                "page_name" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : ''),
                "page_active" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_active'] ?? '') : ''),
                "page_domain_id" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_domain_id'] ?? '') : '')
            ) ;

            if ((is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '') == "") {
                $error = true;
                $tabError['page_name'] = Translate::getInstance()->getText( 'mandatory_fillin');
            }

            if ($error == false) {
                $contentRow->page_name = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_name'] ?? '') : '');
                $contentRow->page_active = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_active'] ?? '') : '');
                $contentRow->page_domain_id = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_domain_id'] ?? '') : '');

                if ( ACTIVE_USER )
                {
                    $contentRow->page_access_user = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_access_user'] ?? '') : '');
                    $contentRow->page_access_user_redirect = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_access_user_redirect'] ?? '') : '');
                    $contentRow->page_access_user_group = serialize( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['page_access_user_group'] ?? '') : '') );
                }

                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info(29, $contentRow->page_name);

                $id = $contentRow->page_id;

                if ((is_array($req->getParsedBody()) ? ($req->getParsedBody()['buttonaction'] ?? '') : '') == "stay")  $url = '/admin/pageadmin/edit/' . $id;
                else                                                $url = '/admin/pageadmin';

                $result['msg'] = Translate::getInstance()->getText( 'msg_special_page_modified');
                $result['result'] = true;
                $result['url'] = \App\Kernel\Factory::getInstance()->Url()->get( $url ) ;

                \App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
            }
        }
    });

	$app->get('/delete/:id', function ($id) use ($app)
	{
		return \App\Kernel\AppContext::twig()->render($res, 'delete.twig', [
			'id'  => $id,
			'url' => \App\Kernel\Factory::getInstance()->Url()->get('admin/pageadmin/delete/' . $id )
		]);
	});

	$app->post('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
		$contentRow = \DB::for_table('page')
			->where_equal('page_id' , $id)
			->find_one();

		if ( $contentRow )
		{
			\App\Kernel\Back\Log::getInstance()->warning( 32 , "#{$contentRow->page_id} - {$contentRow->page_name}" ) ;

			$msg = Translate::getInstance()->getText( 'page_deleted' );
			$ret = true ;
			$contentRow->delete();
		}
		else
		{
			$msg = Translate::getInstance()->getText('delete_error' );
		}


		echo json_encode([
			"msg"    => $msg ,
			"result" => $ret,
			'url'    => \App\Kernel\Factory::getInstance()->Url()->get('admin/pageadmin')
		]) ;
	})->name('page_delete');

});