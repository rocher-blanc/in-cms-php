<?php

$app->group('/parammodule', function () use ($app)
{
    $app->get('/', function () use ($app) {

        $contentRows = \DB::for_table('module')
            ->where_equal('module_active' , 1 )
            ->order_by_asc('module_name')
            ->find_many();

        $tab = [] ;
        if ( $contentRows )
        {
            foreach( $contentRows as $row )
            {
                if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $row->module_class_name ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Back\\" . ucfirst( $row->module_class_name );
                else																			 				 $ControllerClass = '\\' . APP_NAME . '\Kernel\Back\Controller' ;

                $Controller = new $ControllerClass;
                $Controller->setEntityName( $row->module_class_name );
                if ( $Controller->loadEntity() == true )
                {
                    if ( $Controller->getEntity()->hasUrl() )
                    {
                        $tab[] = $row;
                    }
                }
            }
        }

        $app->render('ext/parammodule/index.twig.html', [ "contentRows" => $tab ]);

    })->name('parammodule_index');

    $app->map('/edit/:id', function ($id) use ($app)
    {
        $lang = \App\Kernel\Lang::getInstance()->getAll() ;

        $contentRows = \DB::for_table('module')
            ->where_equal('module_active' , 1 )
            ->where_equal('module_id' , $id )
            ->find_one();

        $post = [];
        $contentLang = [];
        if ( ! $contentRows )
        {
            $Factory = \App\Kernel\Factory::getInstance() ;
            $Factory->Response()->flashAndRedirect( "Ce module n'est actuellement pas disponible" , false , 'ext/parammodule' ) ;
        }
        else
        {
            $one = \DB::for_table('module')->where_id_is( $id )->find_one();
            if ( $app->request->isPost() )
            {
                $one->module_priority = ( $app->request->post('module_priority') == '' ? '0.5' : $app->request->post('module_priority') ) ;
                $one->save();

                foreach( $lang as $l )
                {
                    $langRows = \DB::for_table('module_lang')
                        ->where_equal('module_lang_module_id' , $id )
                        ->where_equal('module_lang_lang_id' , $l->id )
                        ->find_one();

					$url = $app->request->post('module_lang_url_' . $l->url ) ;
                    if ( $url == '' ) $url = $contentRows->module_name ;
					$changeUrl = true ;
					
                    if ( ! $langRows )
					{
						$langRows = \DB::for_table('module_lang')->create();
					}
					else
					{
						if ( $url == $langRows->module_lang_url ) $changeUrl = false ;
					}
					
                    $Factory = \App\Kernel\Factory::getInstance() ;
                    if ( $changeUrl == true ) $url = $Factory->Url()->uniq( $url , $l->id ) ;

                    $langRows->module_lang_module_id    = $id ;
                    $langRows->module_lang_lang_id      = $l->id ;
                    $langRows->module_lang_url          = $url ;
                    $langRows->module_lang_title        = $app->request->post('module_lang_title_' . $l->url ) ;
                    $langRows->module_lang_description  = $app->request->post('module_lang_description_' . $l->url ) ;
                    $langRows->module_lang_keyword      = $app->request->post('module_lang_keyword_' . $l->url ) ;
                    $langRows->save();
                }
            }

            $post['module_name'] = $contentRows->module_name ;
            foreach( $lang as $l )
            {
                $langRows = \DB::for_table('module_lang')
                    ->where_equal('module_lang_module_id' , $id )
                    ->where_equal('module_lang_lang_id' , $l->id )
                    ->find_one();

                $contentLang[ $l->id ]['module_lang_url']           = $langRows->module_lang_url ;
                $contentLang[ $l->id ]['module_lang_title']         = $langRows->module_lang_title ;
                $contentLang[ $l->id ]['module_lang_description']   = $langRows->module_lang_description ;
                $contentLang[ $l->id ]['module_lang_keyword']       = $langRows->module_lang_keyword ;
            }

        }

        $app->render('ext/parammodule/edit.twig.html', [
            'id' => $id ,
            'lang' => $lang ,
            'post' => $post ,
            'priority' =>  $one->module_priority,
            'contentLang' => $contentLang
        ]);
    })->name('parammodule_edit')->via('GET', 'POST');
});