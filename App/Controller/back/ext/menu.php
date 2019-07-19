<?php

use App\Kernel\Front\Translate;

function getTreeMenu($rows, $parent_id = -1)
{
    $tree = [];

    foreach ($rows as $row)
    {
        $row_parent_id = -1;
        if ($row->menu_element_parent_id != NULL)
        {
            $row_parent_id = $row->menu_element_parent_id;
        }
        if( $row_parent_id < $parent_id ) continue;
        if( $row_parent_id > $parent_id ) break;


        $obj = new stdClass();
        if ( $row->menu_element_module_id !== NULL )
        {
            $rst = \DB::for_table('module')
                ->select('module_class_name')
                ->where(array('module_id' => $row->menu_element_module_id))
                ->findOne();
            $obj->class_module = $rst->module_class_name ;
        }

        $obj->id 		= $row->menu_element_id;
        $obj->menuid	= $row->menu_element_menu_id;
        $obj->parent_id = $row_parent_id;
        $obj->label 	= $row->menu_element_lang_label;
        $obj->type 		= $row->menu_element_type;
        $obj->value 	= $row->menu_element_value_id;
        $obj->option 	= $row->menu_element_option;
        $obj->submenu 	= ( $row->menu_element_has_submenu == 0 ? false : true );
        $obj->subpages 	= getTreeMenu($rows, $row->menu_element_id);

        if ( $row->menu_element_has_submenu == 1 && $row->menu_element_option == "all" )
        {
            $obj->content = getElementModule( $row->menu_element_module_id ) ;
        }

        $tree[] = $obj;
    }

    return $tree;
}

function getElementModule( $id )
{
    $mod = \DB::for_table('module')
        ->select('module_class_name')
        ->where(array('module_id' => $id))
        ->findOne();

    if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $mod->module_class_name ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Back\\" . ucfirst( $mod->module_class_name );
    else																			 				 $ControllerClass = '\\' . APP_NAME . '\Kernel\Back\Controller' ;

    $Controller = new $ControllerClass;
    $Controller->setEntityName( $mod->module_class_name );
    if ( $Controller->loadEntity() == true && $Controller->getEntity()->hasUrl() == true )
    {
        // \Debug::dump( $Controller->getEntity()->getUrlName() ) ;

        $id = $Controller->getEntity()->get( $Controller->getEntity()->getIdName() ) ;
        $url = $Controller->getEntity()->get( $Controller->getEntity()->getUrlName() ) ;

        // \Debug::dump( $id );

        $content = \DB::for_module( $Controller->getEntityName() ) ;

        if ( $url->hasLang() )
        {
            $table 			= \DB::getTableName( $Controller->getEntityName() ) ;
            $tableLang  	= \DB::getTableNameLang( $Controller->getEntityName() ) ;
            $idName			= \DB::getIdName( $Controller->getEntityName() ) ;
            $idNameInLang	= \DB::getIdNameInLang( $Controller->getEntityName() ) ;
            $langIdLangName	= \DB::getLangIdLangName( $Controller->getEntityName() ) ;

            $content = $content->select( $tableLang . "." . $url->getColumn() , 'url' )
                ->select( $table . "." . $id->getColumn() , 'id' )
                ->left_outer_join( $tableLang , array( $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ))
                ->where_equal( $tableLang . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getDefault()->id )
                ->order_by_asc( $tableLang . "." . $url->getColumn() )
                ->find_many() ;
        }
        else
        {
            $content = $content->select( $url->getColumn() , 'url' )
                ->select( $id->getColumn() , 'id' )
                ->order_by_asc( $url->getColumn() )
                ->find_many();
        }

        return $content ;
    }

    return false ;
}

function deleteByParent( $idparent , $idmenu )
{
    $contentRow = \DB::for_table('menu_element')
        ->where(['menu_element_parent_id' => $idparent , 'menu_element_menu_id' => $idmenu])
        ->find_many();

    if ( $contentRow )
    {
        foreach( $contentRow as $row )
        {
            deleteByParent( $row->menu_element_id , $idmenu ) ;

            $row->delete();
        }
    }
}

$app->group('/menu', function () use ($app)
{
    $app->get('/construct/:id', function ( $id ) use ($app)
    {
        $lang = \App\Kernel\Lang::getInstance()->getDefault()->id ;

        $contentRows = \DB::for_table('menu_element')
            ->left_outer_join('menu_element_lang', array('menu_element.menu_element_id', '=', 'menu_element_lang.menu_element_lang_menu_element_id'))
            ->where(['menu_element_lang.menu_element_lang_lang_id' => $lang,'menu_element.menu_element_menu_id' => $id])
            ->order_by_asc('menu_element.menu_element_parent_id')
            ->order_by_asc('menu_element.menu_element_order')
            ->find_many();

        $contentRows = getTreeMenu( $contentRows ) ;

        $app->render('ext/menu/construct.twig.html', array( "id" => $id ,  "contentRows" => $contentRows ));
    })->name('menu_construct');

    $app->get('/order/:idmenu/:parent/:token', function ( $menu , $parent , $token ) use ($app)
    {
        $Factory = \App\Kernel\Factory::getInstance();

        if ( $Factory->Token()->check( $token ) == false )
        {
            $Factory->Response()->returnJSON( "Token invalide" ) ;
        }
        else
        {
            $get = $app->request()->get('table-idmenu-' . $menu );
            if ( $get )
            {
                $position = 1;
                foreach( $get as $row )
                {
                    $contentRow = \DB::for_table('menu_element')->where_id_is( $row )->find_one();
                    $contentRow->menu_element_order = $position ;
                    $contentRow->save();
                    $position++;
                }
            }
        }

        $Factory->Response()->returnJSON( "L'ordre a bien été modifié" , true ) ;
    })->name('menu_order');

    $app->get('/', function () use ($app)
    {
        $contentRows = \DB::for_table('menu')
            ->order_by_asc('menu_name')
            ->find_many();

        $app->render('ext/menu/index.twig.html', array( "contentRows" => $contentRows ));
    })->name('menu_index');

    $app->map('/edit(/:id)', function ($id = -1) use ($app)
    {
        $error 	  = false ;
        $tabError = array() ;

        $contentRow = \DB::for_table('menu')
            ->where_id_is($id)
            ->find_one();

        if ( $id != -1 && !$contentRow )
        {
            $app->redirect( $app->config('admin.url') . '/ext/menu');
        }

        if ( $app->request->isPost() )
        {
            $post = array(
                "menu_name" => $app->request->post('menu_name')
            ) ;

            if ( !$contentRow )
            {
                $contentRow = \DB::for_table('menu')->create();
                $add = true ;
            }

            if ( $app->request->post('menu_name') == "" )
            {
                $error = true ;
                $tabError['menu_name'] = Translate::getInstance()->getText('mandatory_fillin');
            }

            if ( $error == false )
            {
                $contentRow->menu_name = $app->request->post('menu_name') ;
                $contentRow->save() ;

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 37 : 35 ) , $contentRow->menu_name ) ;

                $id = $contentRow->menu_id;

                if ( $app->request->post('submit') == "stay" ) 	$url = '/ext/menu/edit/' . $id ;
                else 											$url = '/ext/menu' ;

                $Factory = \App\Kernel\Factory::getInstance() ;
                $Factory->Response()->flashAndRedirect("Le menu a bien été " . ( $add == true ? "ajouté" : "modifié" ) , true , $url ) ;
            }
        }
        else
        {
            $post = $contentRow ;
        }

        $app->render('ext/menu/edit.twig.html', array(
            "post" => $post,
            "id" => $id,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )
        ));
    })->name('menu_edit')->via('GET', 'POST');

    $app->delete('/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('menu')
            ->where_id_is($id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 36 , $contentRow->menu_name ) ;

            $msg = Translate::getInstance()->getText('delete_menu' );
            $ret = true ;
            $contentRow->delete();
        }
        else
        {
            $msg = Translate::getInstance()->getText('delete_error' );
        }

        $Factory = \App\Kernel\Factory::getInstance() ;
        $Factory->Response()->returnJSON( $msg , $ret ) ;
    })->name('menu_delete');

    ###########################################################################################################################
    ###########################################################################################################################
    ###############################                                                        ####################################
    ###############################                  E L E M E N T S                       ####################################
    ###############################                                                        ####################################
    ###########################################################################################################################
    ###########################################################################################################################

    function initElement()
    {
        $lang = \App\Kernel\Lang::getInstance()->getAll() ;

        $module = \DB::for_table('module')
            ->where_equal('module_active',1)
            ->order_by_asc('module_name')
            ->find_many();

        if ( $module )
        {
            foreach( $module as $mod )
            {
                if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . ucfirst( $mod->module_class_name ) . '.php' )) $ControllerClass = "\Project\Module\Controller\Back\\" . ucfirst( $mod->module_class_name );
                else																			 				 $ControllerClass = '\\' . APP_NAME . '\Kernel\Back\Controller' ;

                $Controller = new $ControllerClass;
                $Controller->setEntityName( $mod->module_class_name );
                if ( $Controller->loadEntity() == true && $Controller->getEntity()->hasUrl() == true )
                {
                    $std = new \stdClass;
                    $std->hasParent   		   = false ;
                    $std->module_id   		   = $mod->module_id ;
                    $std->module_name 		   = $mod->module_name ;
                    $std->module_class_name    = $mod->module_class_name ;

                    // \Debug::dump( $Controller->getEntity()->getUrlName() ) ;

                    $id = $Controller->getEntity()->get( $Controller->getEntity()->getIdName() ) ;
                    $url = $Controller->getEntity()->get( $Controller->getEntity()->getUrlName() ) ;

                    // \Debug::dump( $id );

                    $content = \DB::for_module( $Controller->getEntityName() ) ;

                    if ( $url->hasLang() )
                    {
                        $table 			= \DB::getTableName( $Controller->getEntityName() ) ;
                        $tableLang  	= \DB::getTableNameLang( $Controller->getEntityName() ) ;
                        $idName			= \DB::getIdName( $Controller->getEntityName() ) ;
                        $idNameInLang	= \DB::getIdNameInLang( $Controller->getEntityName() ) ;
                        $langIdLangName	= \DB::getLangIdLangName( $Controller->getEntityName() ) ;

                        $content = $content->select( $tableLang . "." . $url->getColumn() , 'url' )
                            ->select( $table . "." . $id->getColumn() , 'id' )
                            ->left_outer_join( $tableLang , array( $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ))
                            ->where_equal( $tableLang . '.' . $langIdLangName , \App\Kernel\Lang::getInstance()->getDefault()->id )
                            ->order_by_asc( $tableLang . "." . $url->getColumn() )
                            ->find_many() ;
                    }
                    else
                    {
                        $content = $content->select( $url->getColumn() , 'url' )
                            ->select( $id->getColumn() , 'id' )
                            ->order_by_asc( $url->getColumn() )
                            ->find_many();
                    }

                    $std->content = $content ;

                    $tab[] = $std ;
                }
            }
        }
        // \Debug::dump( $tab );


        $page = \DB::for_table('page')
            ->where_equal('page_active',1)
            ->order_by_asc('page_name')
            ->find_many();

        return [ 'module' => $tab , 'page' => $page , 'lang' => $lang , 'label' => [] , 'id' => -1 ] ;
    }

    #############################################################
    ########################    DELETE    #######################
    #############################################################

    $app->delete('/element/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('menu_element')
            ->where_id_is($id)
            ->find_one();

        if ( $contentRow )
        {
            $idmenu = $contentRow->menu_element_menu_id ;
            deleteByParent( $id , $idmenu ) ;

            $contentRow->delete();
            $msg = Translate::getInstance()->getText('delete_element');
            $ret = true ;
        }
        else
        {
            $msg = Translate::getInstance()->getText('delete_error');
        }

        $Factory = \App\Kernel\Factory::getInstance() ;
        $Factory->Response()->returnJSON( $msg , $ret ) ;
    })->name('menu_delete');

    #############################################################
    ########################     ADD      #######################
    #############################################################

    $app->get('/addelement/:parent/:menu', function ( $idparent , $idmenu ) use ($app)
    {
        $tab = initElement() ;
        $tab['idmenu']   = $idmenu ;
        $tab['idparent'] = $idparent ;

        $app->render('ext/menu/element_add.twig.html', $tab );
    })->name('menu_addelement');

    $app->post('/element/add', function () use ($app)
    {
        $ret  = true ;
        $msg  = Translate::getInstance()->getText('added_element');
        $lang = \App\Kernel\Lang::getInstance()->getAll() ;

        foreach( $lang as $l )
        {
            if ( $app->request->post('label_' . $l->url ) == "" ) {
                $ret = false ;
                $msg = Translate::getInstance()->getText('fill_fields');
            }
        }

        if ( $ret == true )
        {
            if ( $app->request->post('idparent') == -1 ) 	$parent = NULL;
            else											$parent = $app->request->post('idparent') ;

            $order = \DB::for_table('menu_element') ;
            if ( $parent !== NULL ) $order = $order->where_equal('menu_element_parent_id',$parent) ;
            else					$order = $order->where_null('menu_element_parent_id') ;
            $order = $order->max('menu_element_order') + 1 ;

            switch( $app->request->post('type_element') )
            {
                case "section" :
                    $elm = \DB::for_table('menu_element')->create();
                    $elm->menu_element_menu_id = $app->request->post('idmenu') ;
                    $elm->menu_element_parent_id = $parent ;
                    $elm->menu_element_order = $order ;
                    $elm->menu_element_type = $app->request->post('type_element') ;
                    $elm->menu_element_link_blank = NULL ;
                    $elm->menu_element_link_href = NULL ;
                    $elm->menu_element_module_id = NULL ;
                    $elm->menu_element_value_id = NULL ;
                    $elm->menu_element_max_level = NULL ;
                    $elm->menu_element_has_submenu = NULL ;
                    $elm->menu_element_option = NULL ;
                    $elm->save();

                    foreach( $lang as $l )
                    {
                        $elmLang = \DB::for_table('menu_element_lang')->create();
                        $elmLang->menu_element_lang_lang_id = $l->id ;
                        $elmLang->menu_element_lang_menu_element_id = $elm->menu_element_id ;
                        $elmLang->menu_element_lang_label = $app->request->post( 'label_' . $l->url ) ;
                        $elmLang->save();
                    }
                break;
                case "page" :
                    $elm = \DB::for_table('menu_element')->create();
                    $elm->menu_element_menu_id = $app->request->post('idmenu') ;
                    $elm->menu_element_parent_id = $parent ;
                    $elm->menu_element_order = $order ;
                    $elm->menu_element_type = $app->request->post('type_element') ;
                    $elm->menu_element_link_blank = NULL ;
                    $elm->menu_element_link_href = NULL ;
                    $elm->menu_element_module_id = NULL ;
                    $elm->menu_element_value_id = $app->request->post('page_id') ;
                    $elm->menu_element_max_level = NULL ;
                    $elm->menu_element_has_submenu = NULL ;
                    $elm->menu_element_option = NULL ;
                    $elm->save();

                    foreach( $lang as $l )
                    {
                        $elmLang = \DB::for_table('menu_element_lang')->create();
                        $elmLang->menu_element_lang_lang_id = $l->id ;
                        $elmLang->menu_element_lang_menu_element_id = $elm->menu_element_id ;
                        $elmLang->menu_element_lang_label = $app->request->post( 'label_' . $l->url ) ;
                        $elmLang->save();
                    }
                break;
                case "module" :
                    $elm = \DB::for_table('menu_element')->create();
                    $elm->menu_element_menu_id = $app->request->post('idmenu') ;
                    $elm->menu_element_parent_id = $parent ;
                    $elm->menu_element_order = $order ;
                    $elm->menu_element_type = $app->request->post('type_element') ;
                    $elm->menu_element_link_blank = NULL ;
                    $elm->menu_element_link_href = NULL ;
                    $elm->menu_element_module_id = $app->request->post('module_id') ;
                    $elm->menu_element_value_id = $app->request->post('module_' . $app->request->post('module_id') ) ;
                    $elm->menu_element_max_level = $app->request->post('level') ;
                    $elm->menu_element_has_submenu = ( $app->request->post('view_submenu') == "yes" ? 1 : 0 ) ;
                    $elm->menu_element_option = $app->request->post('module_option') ;
                    $elm->save();

                    foreach( $lang as $l )
                    {
                        $elmLang = \DB::for_table('menu_element_lang')->create();
                        $elmLang->menu_element_lang_lang_id = $l->id;
                        $elmLang->menu_element_lang_menu_element_id = $elm->menu_element_id;
                        $elmLang->menu_element_lang_label = $app->request->post('label_' . $l->url);
                        $elmLang->save();
                    }
                break;
                case "link" :
                    if ( $app->request->post('link') == "" )
                    {
                        $ret = false ;
                        $msg = Translate::getInstance()->getText('fill_fields');
                    }
                    else
                    {
                        $elm = \DB::for_table('menu_element')->create();
                        $elm->menu_element_menu_id = $app->request->post('idmenu') ;
                        $elm->menu_element_parent_id = $parent ;
                        $elm->menu_element_order = $order ;
                        $elm->menu_element_type = $app->request->post('type_element') ;
                        $elm->menu_element_link_blank = $app->request->post('view_link') ;
                        $elm->menu_element_link_href = $app->request->post('link') ;
                        $elm->menu_element_module_id = NULL ;
                        $elm->menu_element_value_id = NULL ;
                        $elm->menu_element_max_level = NULL ;
                        $elm->menu_element_has_submenu = NULL ;
                        $elm->menu_element_option = NULL ;
                        $elm->save();

                        foreach( $lang as $l )
                        {
                            $elmLang = \DB::for_table('menu_element_lang')->create();
                            $elmLang->menu_element_lang_lang_id = $l->id ;
                            $elmLang->menu_element_lang_menu_element_id = $elm->menu_element_id ;
                            $elmLang->menu_element_lang_label = $app->request->post( 'label_' . $l->url ) ;
                            $elmLang->save();
                        }
                    }
                break;
            }
        }

        $Factory = \App\Kernel\Factory::getInstance() ;
        $Factory->Response()->returnJSON( $msg , $ret ) ;
    })->name('menu_add_element');

    #############################################################
    ########################   UPDATE     #######################
    #############################################################

    $app->post('/element/update', function () use ($app)
    {
        $ret  = true ;
        $msg  = Translate::getInstance()->getText('modified_element');
        $lang = \App\Kernel\Lang::getInstance()->getAll() ;

        foreach( $lang as $l )
        {
            if ( $app->request->post('label_' . $l->url ) == "" ) {
                $ret = false ;
                $msg = Translate::getInstance()->getText('fill_fields');
            }
        }

        if ( $ret == true )
        {
            $elm = \DB::for_table('menu_element')->where_id_is($app->request->post('id'))->find_one();

            switch( $app->request->post('type_element') )
            {
                case "page" :
                    $elm->menu_element_type = $app->request->post('type_element') ;
                    $elm->menu_element_link_blank = NULL ;
                    $elm->menu_element_link_href = NULL ;
                    $elm->menu_element_module_id = NULL ;
                    $elm->menu_element_value_id = $app->request->post('page_id') ;
                    $elm->menu_element_max_level = NULL ;
                    $elm->menu_element_has_submenu = NULL ;
                    $elm->menu_element_option = NULL ;
                    break;
                case "module" :
                    $elm->menu_element_type = $app->request->post('type_element') ;
                    $elm->menu_element_link_blank = NULL ;
                    $elm->menu_element_link_href = NULL ;
                    $elm->menu_element_module_id = $app->request->post('module_id') ;
                    $elm->menu_element_value_id = $app->request->post('module_' . $app->request->post('module_id') ) ;
                    $elm->menu_element_max_level = $app->request->post('level') ;
                    $elm->menu_element_has_submenu = ( $app->request->post('view_submenu') == "yes" ? 1 : 0 ) ;
                    $elm->menu_element_option = $app->request->post('module_option') ;
                    break;
                case "link" :
                    if ( $app->request->post('link') == "" )
                    {
                        $ret = false ;
                        $msg = Translate::getInstance()->getText('fill_fields');
                    }
                    else
                    {
                        $elm->menu_element_type = $app->request->post('type_element') ;
                        $elm->menu_element_link_blank = $app->request->post('view_link') ;
                        $elm->menu_element_link_href = $app->request->post('link') ;
                        $elm->menu_element_module_id = NULL ;
                        $elm->menu_element_value_id = NULL ;
                        $elm->menu_element_max_level = NULL ;
                        $elm->menu_element_has_submenu = NULL ;
                        $elm->menu_element_option = NULL ;
                    }
                    break;
            }

            if ( $elm ) $elm->save();

            if ( $ret != false )
            {
                foreach( $lang as $l )
                {
                    $elmLang = \DB::for_table('menu_element_lang')->where(['menu_element_lang_menu_element_id' => $app->request->post('id'), 'menu_element_lang_lang_id' => $l->id])->find_one();
                    if ( ! $elmLang )
                    {
                        $elmLang = \DB::for_table('menu_element_lang')->create();
                        $elmLang->menu_element_lang_menu_element_id = $app->request->post('id');
                        $elmLang->menu_element_lang_lang_id = $l->id;
                    }

                    $elmLang->menu_element_lang_label = $app->request->post( 'label_' . $l->url ) ;
                    $elmLang->save();
                }
            }
        }

        $Factory = \App\Kernel\Factory::getInstance() ;
        $Factory->Response()->returnJSON( $msg , $ret ) ;
    })->name('element_update');

    $app->get('/updateelement/:id', function ( $id ) use ($app)
    {

        $element = \DB::for_table('menu_element')
            ->where_equal('menu_element_id',$id)
            ->find_one();

        $elmLang = \DB::for_table('menu_element_lang')
            ->where_equal('menu_element_lang_menu_element_id',$id)
            ->find_many();

        $label = [];
        foreach( $elmLang as $row )
        {
            $label[ $row->menu_element_lang_lang_id ] = $row->menu_element_lang_label ;
        }

        $tab = initElement() ;
        $tab['idmenu']   = $element->menu_element_menu_id ;
        $tab['idparent'] = $element->menu_element_parent_id ;
        $tab['id'] 		 = $id ;
        $tab['e'] 		 = $element ;
        $tab['label'] 	 = $label ;

        $app->render('ext/menu/element_add.twig.html', $tab );
    })->name('menu_update_element');
});