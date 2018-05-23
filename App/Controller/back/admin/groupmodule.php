<?php

$app->group('/groupmodule', function () use ($app)
{
    $app->get('/', function () use ($app)
    {

        $contentRows = \DB::for_table('module_group')
                          ->order_by_asc('module_group_order')
                          ->find_many();

        $app->render('admin/groupmodule/index.twig.html', array(
            "contentRows" => $contentRows
        ));

    })->name('groupmodule_index');

    $app->get('/bygroup(/:id)', function ( $id = NULL ) use ($app)
    {
        $contentRows = \DB::for_table('module');

        if ( $id !== NULL ) 	$contentRows = $contentRows->where_equal('module_module_group_id',$id) ;
        else					$contentRows = $contentRows->where_null('module_module_group_id') ;

        $contentRows = $contentRows->order_by_asc('module_order')
                                   ->find_many();

        $app->render('admin/groupmodule/menu.twig.html', array( "contentRows" => $contentRows ));

    })->name('groupmodule_module_by_group');

    $app->get('/updateOrder/:idgroup/(:order)', function ( $idgroup , $order = NULL ) use ($app)
    {
        if ( $order === NULL )
        {
            echo '0x0' ;
        }
        else
        {
            if ( $idgroup == 0 ) $idgroup = NULL ;
            if ( strpos( $order , ',' ) !== false )
            {
                $exp = explode( "," , $order ) ;
                $i   = 1;
                foreach( $exp as $row ) {
                    list( $null , $id ) = explode( '-' , $row ) ;
                    $contentRows = \DB::for_table('module')
                                      ->where_id_is( $id )
                                      ->find_one();
                    $contentRows->module_module_group_id = $idgroup ;
                    $contentRows->module_order = $i ;
                    $contentRows->save();
                }
            }
            else
            {
                list( $null , $id ) = explode( "-" , $order ) ;
                $contentRows = \DB::for_table('module')
                                  ->where_id_is( $id )
                                  ->find_one();
                $contentRows->module_module_group_id = $idgroup ;
                $contentRows->module_order = 1 ;
                $contentRows->save();
            }
        }

        $contentRows = \DB::for_table('module');

        if ( $id !== NULL ) 	$contentRows = $contentRows->where_equal('module_module_group_id',$id) ;
        else					$contentRows = $contentRows->where_null('module_module_group_id') ;

        $contentRows = $contentRows->order_by_asc('module_order')
                                   ->find_many();

        $app->render('admin/groupmodule/menu.twig.html', array( "contentRows" => $contentRows ));

    })->name('groupmodule_module_by_group')->via('GET', 'POST');

    $app->get('/delete/:id', function ($id) use ($app) {
        $app->render('common/delete.twig', [
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/admin/groupmodule/delete/' . $id)
        ]);
    });

    $app->post('/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('module_group')
                         ->where_equal('module_group_id' , $id)
                         ->find_one();

        if ( $contentRow )
        {
            $content = \DB::for_table('module_group')
                          ->where_gt('module_group_order' , $contentRow->module_group_order)
                          ->find_many();
            if ( $content )
            {
                foreach( $content as $row )
                {
                    $row->module_group_order--;
                    $row->save();
                }
            }

            // Foreach column columns in group
            $req_columns = \DB::for_table( "module_column" )
                              ->where_equal( "module_column_module_group_id" , $id )
                              ->find_many();
            foreach ( $req_columns as $column )
            {

                // Foreach blocks in column
                $req_blocks = \DB::for_table( "module_column_block" )
                                 ->where_equal( "module_column_block_module_column_id" , $column->module_column_id )
                                 ->find_many();
                foreach ( $req_blocks as $block )
                {

                    // Foreach modules in block
                    $req_modules = \DB::for_table( "module" )
                                      ->where_equal( "module_module_column_block_id" , $block->module_column_block_id )
                                      ->find_many();
                    foreach ( $req_modules as $module )
                    {
                        $module->module_module_column_block_id = NULL;
                        $module->save();
                    }

                    $block->delete();
                }

                $column->delete();
            }

            \App\Kernel\Back\Log::getInstance()->warning( 22 , $contentRow->module_group_name );

            $msg = "Le groupe de modules a bien été supprimé";
            $ret = true;
            $contentRow->delete();
        }
        else
        {
            $msg = "Une erreur est survenue lors de la suppression" ;
        }

        $result['msg'] = $msg;
        $result['result'] = $ret;
        $result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/admin/groupmodule') ;

        \App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
    })->name('groupmodule_delete');

    $app->get('/active/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $module = \DB::for_table('module_group')
                         ->where_equal('module_group_id' , $id)
                         ->find_one();

            if ( $module->module_group_active == 0 ) {
                $module->module_group_active = 1;
                $module->save();

                \App\Kernel\Back\Log::getInstance()->warning( 25 , $module->module_group_name ) ;

                $msg = "Le groupe de modules a bien été activé";
                $ret = true;
            }
            else {
                $msg = "Impossible, le groupe de modules est déja activé";
                $ret = false;
            }
        }
        else {
            $msg = "Le token de sécurité est invalide";
            $ret = false;
        }


        \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/groupmodule' );
    })->name('groupmodule_active');

    $app->get('/disactive/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $module = \DB::for_table('module_group')
                         ->where_equal('module_group_id' , $id)
                         ->find_one();

            if ( $module->module_group_active == 1 ) {
                $module->module_group_active = 0;
                $module->save();

                \App\Kernel\Back\Log::getInstance()->warning( 26 , $module->module_group_name ) ;

                $msg = "Le groupe de modules a bien été désactivé";
                $ret = true;
            }
            else {
                $msg = "Impossible, le groupe de modules est déja désactivé";
                $ret = false;
            }
        }
        else {
            $msg = "Le token de sécurité est invalide";
            $ret = false;
        }

        \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/groupmodule' );
    })->name('groupmodule_disactive');

    $app->map('/edit(/:id)', function ($id = -1) use ($app)
    {
        $error 	  = false ;
        $tabError = array() ;

        $contentRow = \DB::for_table('module_group')
                         ->where_equal('module_group_id' , $id)
                         ->find_one();

        if ( $id != -1 && !$contentRow ) {
            $app->redirect( $app->config('admin.url') . '/admin/groupmodule');
        }

        if ( $app->request->isPost() ) {
            $post = array(
                "module_group_name" => $app->request->post('module_group_name'),
                "module_group_active" => $app->request->post('module_group_active')
            ) ;

            if ( !$contentRow ) {
                $contentRow = \DB::for_table('module_group')->create();
                $add = true ;
            }

            if ( $app->request->post('module_group_name') == "" ) {
                $error = true ;
                $tabError['module_group_name'] = "Veuillez remplir ce champ" ;
            }

            if ( $error == false ) {
                $contentRow->module_group_name 		= $app->request->post('module_group_name') ;
                $contentRow->module_group_active 	= ( $app->request->post('module_group_active') == NULL ? 0 : 1 ) ;
                if ( $add == true )
                {
                    $contentRow->module_group_order = \DB::for_table('module_group')->max('module_group_order') + 1;
                }
                $contentRow->save() ;

                if( $add == true )
                {
                    $prepColumn = \DB::for_table("module_column")->create();
                    $prepColumn->module_column_module_group_id = $contentRow->module_group_id;
                    $prepColumn->save();

                    $prepBlock = \DB::for_table("module_column_block")->create();
                    $prepBlock->module_column_block_module_column_id = $prepColumn->module_column_id;
                    $prepBlock->module_column_block_title            = NULL;
                    $prepBlock->module_column_block_order            = 1;
                    $prepBlock->save();
                }

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 23 : 24 ) , $contentRow->module_group_name ) ;

                $id = $contentRow->module_group_id;

                \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect("Le groupe de modules a bien été " . ( $add == true ? "ajouté" : "modifié" ) , true , '/admin/groupmodule' );
            }
        }
        else {
            $post = $contentRow ;
        }

        $app->render('admin/groupmodule/edit.twig.html', array(
            "post"       => $post,
            "id"         => $id,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )
        ));

    })->name('groupmodule_edit')->via('GET', 'POST');

    $app->group('/order', function () use ($app)
    {
        $app->get('/up/:id/:token', function ($id,$token) use ($app)
        {
            if ( $token == $_SESSION[ $app->config('token') ] ) {
                $modgroup = \DB::for_table('module_group')
                    ->where_equal('module_group_id' , $id)
                    ->find_one();

                if ( $modgroup->module_group_order != 1 ) {
                    $sup = $modgroup->module_group_order - 1;
                    $modgroupSup = \DB::for_table('module_group')
                        ->where_equal('module_group_order' , $sup)
                        ->find_one();
                    $modgroupSup->module_group_order = $modgroup->module_group_order;
                    $modgroupSup->save();

                    $modgroup->module_group_order = $sup;
                    $modgroup->save();

                    \App\Kernel\Back\Log::getInstance()->warning( 27 , $modgroup->module_group_name ) ;

                    $msg = 'La position du groupe de modules a été modifiée';
                    $ret = true;
                }
                else {
                    $msg = 'Impossible, le groupe de modules est déja au niveau le plus haut';
                    $ret = false;
                }
            }
            else {
                $msg = "Le token de sécurité est invalide";
                $ret = false;
            }

            \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/groupmodule' );
        })->name('groupmodule_up');

        $app->get('/down/:id/:token', function ($id,$token) use ($app)
        {
            if ( $token == $_SESSION[ $app->config('token') ] ) {
                $max = \DB::for_table('module_group')->max('module_group_order');

                $modgroup = \DB::for_table('module_group')
                    ->where_equal('module_group_id' , $id)
                    ->find_one();

                if ( $modgroup->module_group_order < $max ) {
                    $sup = $modgroup->module_group_order + 1;
                    $modgroupSup = \DB::for_table('module_group')
                        ->where_equal('module_group_order' , $sup)
                        ->find_one();
                    $modgroupSup->module_group_order = $modgroup->module_group_order;
                    $modgroupSup->save();

                    $modgroup->module_group_order = $sup;
                    $modgroup->save();

                    \App\Kernel\Back\Log::getInstance()->warning( 28 , $modgroup->module_group_name ) ;

                    $msg = 'La position du groupe de modules a été modifiée';
                    $ret = true;
                }
                else {
                    $msg = 'Impossible, le groupe de modules est déja au niveau le plus bas' . $modgroup->module_group_order;
                    $ret = false;
                }
            }
            else {
                $msg = "Le token de sécurité est invalide";
                $ret = false;
            }


            \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/groupmodule' );
        })->name('groupmodule_down');

    });

    $app->get('/menu(/:id)', function ($id = -1) use ($app)
    {
        $error 	  = false ;
        $tabError = array() ;

        $contentRow = \DB::for_table('module_group')
                         ->where_equal('module_group_id' , $id)
                         ->find_one();

        if ( $id != -1 && !$contentRow ) {
            $app->redirect( $app->config('admin.url') . '/admin/groupmodule');
        }

        $blocks_list = [];

        // Get columns
        $req_columns = \DB::for_table( "module_column" )
                          ->where_equal( "module_column_module_group_id", $contentRow->module_group_id )
                          ->find_many() ;
        $columns = [];
        foreach( $req_columns as $column )
        {
            $column = [
                'id'     => $column->module_column_id,
                'blocks' => []
            ];

            // Get column blocks
            $req_block = \DB::for_table( "module_column_block" )
                            ->where_equal( "module_column_block_module_column_id", $column['id'] )
                            ->order_by_asc( "module_column_block_order" )
                            ->find_many() ;
            foreach( $req_block as $block )
            {
                $block = [
                    'id'    => $block->module_column_block_id,
                    'title' => $block->module_column_block_title,
                    'order' => $block->module_column_block_order,
                ];

                $blocks_list[] = $block['id'];
                $column['blocks'][] = $block;
            }

            $columns[] = $column;
        }

        // Get modules
        $req_modules = \DB::for_table('module')
                          ->where_in( "module_module_column_block_id", $blocks_list )
//                          ->where_null( "module_module_column_block_id" )
                          ->order_by_asc( "module_order" )
                          ->find_many();
        $modules = [];
        foreach( $req_modules as $module ) {
            $modules[] = [
                'id' => $module->module_id ,
                'title' => $module->module_name ,
                'order' => $module->module_order ,
                'block' => $module->module_module_column_block_id ,
            ];
        }
        $req_modules_noplace = \DB::for_table('module')
                          ->where_null( "module_module_column_block_id" )
                          ->order_by_asc( "module_order" )
                          ->find_many();
        foreach( $req_modules_noplace as $module )
        {
            $modules[] = [
                'id'    => $module->module_id,
                'title' => $module->module_name,
                'order' => $module->module_order,
                'block' => 0
            ];
        }

        $app->render('admin/groupmodule/menu.twig.html', array(
            "id"           => $id,
            "columns"      => $columns,
            "arrayModules" => $modules,
            "error"		   => ( $error === false ? "0" : "1" ),
            "tabError"	   => json_encode( $tabError )
        ));

    })->name('groupmodule_menu');

    /*----------------------------------------------------------------------*/
    /*----------                                                  ----------*/
    /*----------                 COLUMNS REQUESTS                 ----------*/
    /*----------                                                  ----------*/
    /*----------------------------------------------------------------------*/

    $app->post('/add-column', function() use ($app) {
        // Create column
        $prep_column = \DB::for_table( "module_column" )->create();
        $prep_column->module_column_module_group_id = $app->request->post('group');
        $prep_column->save();

        // Create block
        $prep_block = \DB::for_table( "module_column_block" )->create();
        $prep_block->module_column_block_module_column_id = $prep_column->module_column_id;
        $prep_block->module_column_block_title            = NULL;
        $prep_block->module_column_block_order            = 1;
        $prep_block->save();

        echo json_encode([
            'result'    => true,
            'msg'       => "",
            'column_id' => $prep_column->module_column_id,
            'block_id'  => $prep_block->module_column_block_id,
        ]);
    });


    $app->post('/delete-column', function() use ($app) {
        $column_id = $app->request->post('column_id');

        // Find blocks into this column
        $req_blocks = \DB::for_table( "module_column_block" )
            ->where_equal( "module_column_block_module_column_id", $column_id )
            ->find_many();

        if( $req_blocks )
        {
            foreach( $req_blocks as $block )
            {
                // Remove modules from this block before delete him
                $req_modules = \DB::for_table( "module" )
                    ->where_equal( "module_module_column_block_id", $block->module_column_block_id )
                    ->find_many();
                if( $req_modules )
                {
                    foreach( $req_modules as $module )
                    {
                        $module->module_module_column_block_id = NULL;
                        $module->save();
                    }
                }

                $block->delete();
            }
        }

        // Delete this column
        $one = \DB::for_table( "module_column" )
            ->find_one( $column_id );
        $group_id = $one->module_column_module_group_id;
        $one->delete();

        // Get columns of group for counting
        $columns = \DB::for_table( "module_column" )
            ->where_equal( "module_column_module_group_id", $group_id )
            ->find_many();

        $only_one_column = false;

        // If only one column
        if( count($columns) == 1 )
        {
            $only_one_column = true;
            $column = $columns[0];

            // get blocks in this column
            $blocks = \DB::for_table( "module_column_block" )
                ->where_equal( "module_column_block_module_column_id", $column->module_column_id )
                ->order_by_asc( "module_column_block_order" )
                ->find_many();

            // if many blocks in this column
            if( count($blocks) > 1 )
            {
                $main_block = $blocks[0];

                // Foreach blocks without first block
                for( $i = 1 ; $i < count($blocks) ; $i++ )
                {

                    // Get modules in this block
                    $modules = \DB::for_table( "module" )
                        ->where_equal( "module_module_column_block_id" , $blocks[$i]->module_column_block_id )
                        ->find_many();

                    if( $modules )
                    {
                        foreach( $modules as $mod )
                        {
                            $mod->module_module_column_block_id = $main_block->module_column_block_id;
                            $mod->save();
                        }
                    }

                    $blocks[$i]->delete();
                }
            }
        }

        \App\Kernel\Factory::getInstance()->Response()->printJSON([
            'result'    => true,
            'msg'       => "La colonne a bien été supprimée",
        ]) ;
    });




    /*----------------------------------------------------------------------*/
    /*----------                                                  ----------*/
    /*----------                  BLOCKS REQUESTS                 ----------*/
    /*----------                                                  ----------*/
    /*----------------------------------------------------------------------*/

    $app->post('/add-block', function() use ($app) {
        $prep = \DB::for_table( "module_column_block" )->create();
        $prep->module_column_block_module_column_id = $app->request->post('column_id');
        $prep->module_column_block_title            = NULL;
        $prep->module_column_block_order            = $app->request->post('order');
        $prep->save();

        echo json_encode([
            'result'    => true,
            'msg'       => "",
            'block_id'  => $prep->module_column_block_id
        ]);
    });


    $app->post('/rename-block', function() use ($app) {
        $one = \DB::for_table( "module_column_block" )
            ->find_one( $app->request->post('block_id') );
        $one->module_column_block_title = $app->request->post('title');
        $one->save();

        echo json_encode([
            'result'    => true,
            'msg'       => "",
        ]);
    });


    $app->post('/order-block', function() use ($app) {
        $new_order = explode( ",", $app->request->post('new_order') );

        $many = \DB::for_table( "module_column_block" )
            ->where_in( "module_column_block_id", $new_order )
            ->find_many();

        foreach( $many as $column ) {
            $column->module_column_block_module_column_id = $app->request->post('column_id');
            $column->module_column_block_order            = array_search( $column->module_column_block_id, $new_order ) + 1;
            $column->save();
        }
//
        echo json_encode([
            'result'    => true,
            'msg'       => "",
        ]);
    });


    $app->post('/delete-block', function() use ($app) {
        $block_id = $app->request->post('block_id');

        // Remove modules from this block before delete him
        $req_modules = \DB::for_table( "module" )
            ->where_equal( "module_module_column_block_id", $block_id )
            ->find_many();

        if( $req_modules )
        {
            foreach( $req_modules as $module )
            {
                $module->module_module_column_block_id = NULL;
                $module->save();
            }
        }

        // Delete this block
        $one = \DB::for_table( "module_column_block" )
            ->find_one( $block_id );
        $one->delete();

        echo json_encode([
            'result'    => true,
            'msg'       => "",
        ]);
    });




    /*----------------------------------------------------------------------*/
    /*----------                                                  ----------*/
    /*----------                 MODULES REQUESTS                 ----------*/
    /*----------                                                  ----------*/
    /*----------------------------------------------------------------------*/

    $app->post('/order-module', function() use ($app) {
        $new_order = explode( ",", $app->request->post('new_order') );
        $block_id = $app->request->post('block_id');

        if( $block_id == 0 )
        {
            $block_id = NULL;
        }

        $many = \DB::for_table( "module" )
            ->where_in( "module_id", $new_order )
            ->find_many();


        foreach( $many as $column ) {
            $column->module_module_column_block_id = $block_id;
            $column->module_order                  = array_search( $column->module_id, $new_order ) + 1;
            $column->save();
        }

        echo json_encode([
            'result'    => true,
            'msg'       => "",
        ]);
    });



});