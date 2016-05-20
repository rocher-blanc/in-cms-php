<?php

// INDEX
$app->get('/', function () use ( $app ) {
    $json = \App\Kernel\Factory::getInstance()->File()->read( APPLICATION_PATH . '/../composer.json');
    $json = json_decode( $json ) ;

    $data = DB::for_table('param')
        ->where_equal('param_key', 'server_cdn')
        ->find_one();

    if ( $data )    $cdn = $data->param_value ;
    else            $cdn = "" ;

    $seo_page = DB::for_table('page')
        ->left_outer_join( 'page_lang' , [ 'page.page_id' , '=', 'page_lang.page_lang_page_id' ] )
        ->where_equal('page.page_active', 1)
        ->where_in('page_lang.page_lang_lang_id',\App\Kernel\Lang::getInstance()->getTabLang() )
        ->where_raw("((page_lang.page_lang_title IS NULL OR page_lang.page_lang_description IS NULL OR page_lang.page_lang_keyword IS NULL) OR (page_lang.page_lang_title = '' OR page_lang.page_lang_description = '' OR page_lang.page_lang_keyword = ''))",[])
        ->group_by('page.page_id')
        ->find_many();

    $contentRows = \DB::for_table('module')
        ->where_equal('module_active' , 1 )
        ->order_by_asc('module_name')
        ->find_many();

    $module = [] ;
    $tab    = [] ;

    if ( $contentRows )
    {
        foreach( $contentRows as $row )
        {
            $entityName = ucfirst( $row->module_class_name ) ;

            if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $row->module_class_name ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Back\\" . $entityName;
            else																			 				 $ControllerClass = '\\' . APP_NAME . '\Kernel\Back\Controller' ;

            $Controller = new $ControllerClass;
            $Controller->setEntityName( $row->module_class_name );
            if ( $Controller->loadEntity() == true )
            {
                if ( $Controller->getEntity()->hasUrl() )
                {
                    $tab[] = $row->module_id;

                    $urlField = $Controller->getEntity()->get( $Controller->getEntity()->getUrlName() ) ;

                    $tbl    = DB::getTableName( $entityName );
                    $idName	= \DB::getIdName( $entityName ) ;

                    if ( ! $urlField->hasLang() )
                    {
                        $tblField = DB::getTableName( $entityName );
                    }
                    else
                    {
                        $tblField = DB::getTableNameLang( $entityName );
                    }

                    $seo_module_one = DB::for_module( $entityName )
                        ->select( 'seo.seo_title' )
                        ->select( 'seo.seo_description' )
                        ->select( 'seo.seo_keyword' )
                        ->select( $tbl . '.' . $idName , 'id' )
                        ->select( $tblField . '.' . $urlField->getColumn() , 'value' )
                        ->left_outer_join( 'seo' , [ 'seo.seo_element_id' , '=', $tbl . '.' . $idName ] );

                    if ( $urlField->hasLang() )
                    {
                        $seo_module_one->left_outer_join( $tblField , array( $tbl . '.' . $idName , '=', $tblField . '.' . DB::getIdNameInLang( $entityName ) ))
                            ->where_equal($tblField . '.' . DB::getLangIdLangName( $entityName ) , \App\Kernel\Lang::getInstance()->getDefault()->id);
                    }

                    $seo_module_one->where_equal('seo.seo_module_id', $row->module_id)
                        ->where_in('seo.seo_lang_id',\App\Kernel\Lang::getInstance()->getTabLang() )
                        ->where_raw("(seo.seo_title IS NULL OR seo.seo_description IS NULL OR seo.seo_keyword IS NULL)",[])
                        ->group_by('seo.seo_element_id')
                        ->find_many();

                    if ( $seo_module_one )
                    {
                        $a = [
                            'title' => $Controller->getEntity()->get( $Controller->getEntity()->getUrlName() )->getTitle(),
                            'name' => $row->module_name,
                            'icon' => $row->module_icon,
                            'class' => $row->module_class_name,
                            'tab' => $seo_module_one,
                        ];

                        $module[] = $a;
                    }
                }
            }
            unset( $Controller );
        }
    }

    if ( $tab )
    {
        $seo_module = DB::for_table('module')
            ->left_outer_join( 'module_lang' , [ 'module.module_id' , '=', 'module_lang.module_lang_module_id' ] )
            ->where_in('module.module_id', $tab)
            ->where_in('module_lang.module_lang_lang_id',\App\Kernel\Lang::getInstance()->getTabLang() )
            ->where_raw("((module_lang.module_lang_title IS NULL OR module_lang.module_lang_description IS NULL OR module_lang.module_lang_keyword IS NULL) OR (module_lang.module_lang_title = '' OR module_lang.module_lang_description = '' OR module_lang.module_lang_keyword = ''))",[])
            ->group_by('module.module_id')
            ->find_many();
    }
    else
    {
        $seo_module = [];
    }

    $app->render('index/index.twig.html' , [
        "version" => $json->version,
        "debug" => DEBUG,
        "seo" => [
            "page" => $seo_page,
            "module" => $seo_module,
            "elt_module" => $module
        ],
        "cdn" => $cdn,
        "date_update" => filemtime( VENDOR_PATH . '/autoload.php' ),
    ]) ;
})->name('index');