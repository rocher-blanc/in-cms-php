<?php

$app->group('/pageadmin', function () use ($app)
{
	$app->get('/', function () use ($app)
	{
        $domain = false ;

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
            }
        }
        else
        {
            // pas de gestion de domaines
            $content = \DB::for_table('page')
                ->order_by_asc('page_name')
                ->find_many();
        }
		
		$app->render('admin/pageadmin/index.twig.html', [
            'domain' => $domain,
            'content' => $content
        ]);
	})->name('page_index');




/*


	$app->map('/edit(/:id)', function ($id = -1) use ($app)
	{
		$tabFolders = unserialize( CONTROLLER_FOLDERS_PATH ) ;
		$arrayController = [];

		foreach( $tabFolders as $folder )
		{
			$scan = glob( $folder . '/*.php' ) ;
			if ( ! empty( $scan ) )
			{
				foreach( $scan as $row )
				{
					$controller = str_replace( $folder . '/' , '' , $row ) ;
					$arrayController[ $controller ] = $controller ;
				}
			}
		}

		if ( empty( $arrayController ) )
		{
			$Factory = \App\Kernel\Factory::getInstance() ;
			$Factory->Response()->flashAndRedirect("Il n'y a actuellement aucun controller de présent dans le projet", false , 'ext/page' ) ;
		}
		else
		{
			$controllerRow = \DB::for_table('page')
				->select('page_id')
				->select('page_controller')
				->find_many();

			if ( $controllerRow )
			{
				foreach( $controllerRow as $row )
				{
					if ( $row->page_id != $id ) unset( $arrayController[ $row->page_controller ] ) ;
				}
			}

			if ( count( $arrayController ) == 0 )
			{
				$Factory = \App\Kernel\Factory::getInstance() ;
				$Factory->Response()->flashAndRedirect( "Il n'y a plus de controller libre dans le projet" , false , 'admin/pageadmin' ) ;
			}
		}

		$lang 	  = \App\Kernel\Lang::getInstance()->getAll() ;
		$error 	  = false ;
		$tabError = array() ;

		$contentRow = \DB::for_table('page')
			->where_equal('page_id' , $id)
			->find_one();

		if ( $id != -1 && !$contentRow )
		{
			$app->redirect( $app->config('admin.url') . '/pageadmin/page');
		}

		if ( $app->request->isPost() )
		{
			$post = array(
				"page_name" => $app->request->post('page_name'),
				"page_controller" => $app->request->post('page_controller'),
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

			if ( $app->request->post('page_controller') == "" )
			{
				$error = true ;
				$tabError['page_controller'] = "Veuillez sélectionner un élement" ;
			}

			if ( $error == false )
			{
				$contentRow->page_name 			= $app->request->post('page_name') ;
				$contentRow->page_controller 	= $app->request->post('page_controller') ;
				$contentRow->page_active 		= ( $app->request->post('page_active') == NULL ? 0 : 1 ) ;

				if ( $forceActive == true ) $contentRow->page_active = 1;

				$contentRow->save() ;

				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 33 : 29 ) , $contentRow->page_name ) ;

				$id = $contentRow->page_id;

				if ( $app->request->post('submit') == "stay" ) 	$url = '/admin/pageadmin/edit/' . $id ;
				else 											$url = '/admin/pageadmin' ;

				$Factory = \App\Kernel\Factory::getInstance() ;
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

		$app->render('admin/pageadmin/edit.twig.html', array(
			"post" => $post,
			"id" => $id,
			"arrayController" => $arrayController,
			"lang" => $lang,
			"contentLang" => $contentLang,
			"error"		 => ( $error === false ? "0" : "1" ),
			"tabError"	 => json_encode( $tabError )
		));
	})->name('page_edit')->via('GET', 'POST');



*/






	#########################################################################################################

	$app->map('/edit', function () use ($app)
	{
		$error 	  = false ;
		$tabError = array() ;

		$post = array(
			"page_name" => $app->request->post('page_name'),
			"page_controller" => $app->request->post('page_controller'),
			"page_active" => $app->request->post('page_active')
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

		if ( $app->request->isPost() ) {
			$contentRow = \DB::for_table('page')->create();

			$ct = \DB::for_table('page')->count();

			if ($ct == 0) {
				$contentRow->page_default = 1;
				$forceActive = true;
			}

			if ($app->request->post('page_name') == "") {
				$error = true;
				$tabError['page_name'] = "Veuillez remplir ce champ";
			}

			if ($error == false) {
				$contentRow->page_name = $app->request->post('page_name');
				$contentRow->page_active = ($app->request->post('page_active') == NULL ? 0 : 1);
                $contentRow->page_domain_id = ( $reqDomains ? $app->request->post('page_domain_id') : NULL );
				$contentRow->save();

				\App\Kernel\Back\Log::getInstance()->info(33, $contentRow->page_name);

				$id = $contentRow->page_id;

				if ($app->request->post('submit') == "stay") $url = '/admin/pageadmin/edit/' . $id;
				else                                         $url = '/admin/pageadmin';

				/* ************ Création du controller PHP ************ */
				$php = '' ;
				$php.= "<"."?"."php\n\n" ;
				$php.= "namespace Project\Controller\Front;\n\n" ;
				$php.= "class Page" . $id . " extends \App\Kernel\Front\Page\n" ;
				$php.= "{\n" ;
				$php.= "\t\n" ;
				$php.= "}" ;
				if ( ! file_exists( PROJECT_PATH . "/Controller/Front/Page" . $id . ".php" ) ) \App\Kernel\Factory::getInstance()->File()->create( PROJECT_PATH . "/Controller/Front/Page" . $id . ".php" , $php );

				/* ************ Création du template Twig ************ */
				$tpl = '{% extends \'base.twig.html\' %}' . "\n";
				$tpl.= '{% block contenu %}Page ' . $id . '{% endblock %}' . "\n";
				if ( ! file_exists( PROJECT_PATH . "/view/front/page/page-" . $id . ".twig.html" ) ) \App\Kernel\Factory::getInstance()->File()->create( PROJECT_PATH . "/view/front/page/page-" . $id . ".twig.html" , $tpl );


				/* ************ Création de base.twig.html s'il n'existe pas ************ */
				$tpl = "{% extends 'layout.twig.html' %}\n" ;
				$tpl.= '{% block content %}' . "\n";
				$tpl.= "\t{{ block('contenu') }}\n" ;
				$tpl.= '{% endblock %}' . "\n";
				if ( ! file_exists( PROJECT_PATH . "/view/front/base.twig.html" ) ) \App\Kernel\Factory::getInstance()->File()->create( PROJECT_PATH . "/view/front/base.twig.html" , $tpl );

				$Factory = \App\Kernel\Factory::getInstance();
				$Factory->Response()->flashAndRedirect("La page spéciale a bien été ajoutée", true, $url);
			}
		}

        $app->render('admin/pageadmin/edit.twig.html', array(
            "post"       => $post,
            "id"         => -1,
            "domains"    => $domains,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError ),
    ));

	})->name('page_add')->via('GET', 'POST');

	$app->map('/edit/:id', function ( $id ) use ($app)
	{
		$error 	  = false ;
		$tabError = array() ;

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

		$post = array(
			"page_name" => $contentRow->page_name,
			"page_active" => $contentRow->page_active,
            "page_domain_id" => $contentRow->page_domain_id,
		) ;
        if( !empty($domains) && in_array( $post['page_domain_id'] , array_keys($domains) ) )
        {
            $domains[ $post['page_domain_id'] ]['selected'] = true;
        }

		if ( $app->request->isPost() ) {
			$post = array(
				"page_name" => $app->request->post('page_name'),
				"page_active" => $app->request->post('page_active'),
				"page_domain_id" => $app->request->post('page_domain_id')
			) ;

			if ($app->request->post('page_name') == "") {
				$error = true;
				$tabError['page_name'] = "Veuillez remplir ce champ";
			}

			if ($error == false) {
				$contentRow->page_name = $app->request->post('page_name');
				$contentRow->page_active = ($app->request->post('page_active') == NULL ? 0 : 1);
				$contentRow->page_domain_id = $app->request->post('page_domain_id');
				$contentRow->save();

				\App\Kernel\Back\Log::getInstance()->info((33), $contentRow->page_name);

				$id = $contentRow->page_id;

				if ($app->request->post('submit') == "stay") $url = '/admin/pageadmin/edit/' . $id;
				else                                         $url = '/admin/pageadmin';

				$Factory = \App\Kernel\Factory::getInstance();
				$Factory->Response()->flashAndRedirect("La page spéciale a bien été ajoutée", true, $url);
			}
		}

		$app->render('admin/pageadmin/edit.twig.html', array(
			"post"       => $post,
			"id"         => $id,
            "domains"    => $domains,
			"error"		 => ( $error === false ? "0" : "1" ),
			"tabError"	 => json_encode( $tabError )
		));

	})->name('page_edit')->via('GET', 'POST');
});