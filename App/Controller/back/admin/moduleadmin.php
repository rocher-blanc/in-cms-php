<?php

$app->group('/moduleadmin', function () use ($app)
{
	$app->get('/', function () use ($app)
	{

        $contentRows = \DB::for_table('module')
								->order_by_asc('module_active')
								->order_by_asc('module_name')
								->find_many();

        $tab = [];

        if ( $contentRows )
        {
            foreach( $contentRows as $row )
            {
                $tab[ $row->module_class_name ] = $row->module_class_name;
            }
        }

        $files = glob( ENTITY_PATH . "/*.php");
        $list = [];

        if ( $files )
        {
            foreach( $files as $file )
            {
                $name = str_replace( ENTITY_PATH . "/" , '' , $file );
                $name = str_replace( ".php" , '' , $name );

                if ( ! array_key_exists( $name , $tab ) ) $list[] = $name ;
            }
        }

        $app->render('admin/moduleadmin/index.twig.html', [
            "contentRows" => $contentRows ,
            "noInstall" => $list
        ]);

	})->name('moduleadmin_index');

    $app->get('/install/:name', function ( $name ) use ($app)
    {
        $contentRow = \DB::for_table('module')
            ->where_equal('module_class_name',$name)
            ->find_one();

        if ( ! $contentRow )
        {
            $contentRow = DB::for_table('module')->create();
            $contentRow->module_name 		= $name ;
            $contentRow->module_class_name 	= $name ;
            $contentRow->module_icon 		= "cloud" ;
            $contentRow->module_active 		= 1 ;
            $contentRow->save() ;

            $tab = ["Back","Front"];

            // On génére le repository
            foreach( $tab as $row )
            {
                $php = '' ;
                $php.= "<"."?"."php\n\n" ;
                $php.= "namespace Project\Module\Repository\\" . $row . ";\n\n" ;
                $php.= "class " . $name . " extends \App\Kernel\\" . $row . "\Repository\n" ;
                $php.= "{\n" ;
                $php.= "\t\n" ;
                $php.= "}" ;
                if ( ! file_exists( REPOSITORY_PROJECT_PATH . "/" . $row . "/" . $name . ".php" ) ) \App\Kernel\Factory::getInstance()->File()->create( REPOSITORY_PROJECT_PATH . "/" . $row . "/" . $name . ".php" , $php );
            }

            // On génére le controller
            foreach( $tab as $row )
            {
                $php = '' ;
                $php.= "<"."?"."php\n\n" ;
                $php.= "namespace Project\Module\Controller\Front;\n\n" ;
                $php.= "class " . $name . " extends \App\Kernel\Front\Controller\n" ;
                $php.= "{\n" ;
                $php.= "\t\n" ;
                $php.= "}" ;

                $php = '' ;
                $php.= "<"."?"."php\n\n" ;
                $php.= "namespace Project\Module\Controller\\" . $row . ";\n\n" ;
                $php.= "class " . $name . " extends \App\Kernel\\" . $row . "\Controller\n" ;
                $php.= "{\n" ;
                $php.= "\t\n" ;
                $php.= "}" ;
                if ( ! file_exists( MODULE_PATH . "/Controller/" . $row . "/" . $name . ".php" ) ) \App\Kernel\Factory::getInstance()->File()->create( MODULE_PATH . "/Controller/" . $row . "/" . $name . ".php" , $php );
            }

            // On génére la class entity
            $php = '' ;
            $php.= "<"."?"."php\n\n" ;
            $php.= "namespace Project\Module\Entity\Class\\" . $row . ";\n\n" ;

            $php.= "/** @Entity @Table(name=\"addresses\") */\n" ;
            $php.= "class " . $name . " extends \App\Kernel\\" . $row . "\Controller\n" ;
            $php.= "{\n" ;
            $php.= "\t\n" ;
            $php.= "}" ;
            if ( ! file_exists( ENTITIES_PROJECT_PATH . "/" . $name . ".php" ) ) \App\Kernel\Factory::getInstance()->File()->create( ENTITIES_PROJECT_PATH . "/" . $name . ".php" , $php );

            $app->flash('__msg',addslashes( json_encode( "Le module a bien été installé") ) );
            $app->flash('__result',true);
            $app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
        }
        else
        {
            $app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
        }

    })->name('moduleadmin_install');

    $app->delete('/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('module')
            ->where_equal('module_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 20 , $contentRow->module_name ) ;

            $msg = "Le module a bien été supprimé" ;
            $ret = true ;
            $contentRow->delete();
        }
        else
        {
            $msg = "Une erreur est survenue lors de la suppression" ;
        }

        echo json_encode( array( "msg" => $msg , "result" => $ret ) ) ;
    })->name('moduleadmin_delete');

    $app->get('/truncate/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $module = \DB::for_table('module')
                ->where_equal('module_id' , $id)
                ->find_one();

            $entityName = ucfirst( $module->module_class_name ) ;

            if ( file_exists( PROJECT_CONTROLLER_PATH . '/' . $entityName . '.php' )) $ControllerClass = "\Project\Module\Controller\Back\\" . $entityName;
            else																	  $ControllerClass = '\\' . APP_NAME . '\Kernel\Back\Controller' ;

            $Controller = new $ControllerClass;
            $Controller->setEntityName( $entityName );
            if ( $Controller->loadEntity() == true )
            {
                // on vide la table
                \DB::truncate( \DB::getTableName( $Controller->getEntityName() ) );

                // on vide la table multi langue
                if ( $Controller->getEntity()->hasMultilang() )
                {
                    \DB::truncate( \DB::getTableNameLang( $Controller->getEntityName() ) );
                }

                if ( ! empty( $Controller->getEntity()->getField() ) )
                {
                    foreach( $Controller->getEntity()->getField() as $field )
                    {
                        // on vide la table d'association (checkbox)
                        if ( $field->getType() == 'checkbox' )
                        {
                            \DB::truncate( \DB::getTableNameAssoc( $Controller->getEntityName() ) );
                        }
                        // on check s'il y a des champs images
                        else if ( $field->getType() == 'image' )
                        {
                            // On va chercher les images du module
                            $Media = new \App\Kernel\Back\Media;
                            $Media->setModuleId( $module->module_id ) ;
                            $images = $Media->getAll() ;

                            if ( $images )
                            {
                                foreach( $images as $img )
                                {
                                    // On les supprime
                                    $MediaTmp = new \App\Kernel\Back\Media;
                                    $MediaTmp->setFolder( $Controller->getEntity()->getFolder() ) ;
                                    $MediaTmp->setImageId( $img->media_id ) ;
                                    $MediaTmp->delete();
                                }
                            }
                        }
                    }
                }
            }

            \App\Kernel\Back\Log::getInstance()->alert( 45 , $module->module_name ) ;

            $msg = "Le module a bien été vidé";
            $ret = true;
        }
        else {
            $msg = "Le token de sécurité est invalide";
            $ret = false;
        }

        $app->flash('__msg',addslashes( json_encode( $msg )));
        $app->flash('__result',$ret);
        $app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
    })->name('moduleadmin_truncate');

    $app->get('/active/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $module = \DB::for_table('module')
                ->where_equal('module_id' , $id)
                ->find_one();

            if ( $module->module_active == 0 ) {
                $module->module_active = 1;
                $module->save();

                \App\Kernel\Back\Log::getInstance()->warning( 18 , $module->module_name ) ;

                $msg = "Le module a bien été activé";
                $ret = true;
            }
            else {
                $msg = "Impossible, le module est déja activé";
                $ret = false;
            }
        }
        else {
            $msg = "Le token de sécurité est invalide";
            $ret = false;
        }

        $app->flash('__msg',addslashes( json_encode( $msg )));
        $app->flash('__result',$ret);
        $app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
    })->name('moduleadmin_active');

    $app->get('/default/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $module = \DB::for_table('module')
                ->where_equal('module_id' , $id)
                ->find_one();

            $ct = \DB::for_table('module')->where_equal('module_default',1)->count();

            if ( $ct == 0 ) {
                if ( $module->module_default == 0 ) {
                    $module->module_default = 1;
                    $module->save();

                    \App\Kernel\Back\Log::getInstance()->warning( 38 , $module->module_name ) ;

                    $msg = "Le module est maintenant le principal";
                    $ret = true;
                }
                else {
                    $msg = "Impossible, le module est déja le principal";
                    $ret = false;
                }
            }
            else {
                $msg = "Impossible, un module est déja le principal";
                $ret = false;
            }
        }
        else {
            $msg = "Le token de sécurité est invalide";
            $ret = false;
        }

        $app->flash('__msg',addslashes( json_encode( $msg )));
        $app->flash('__result',$ret);
        $app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
    })->name('moduleadmin_default');

    $app->get('/notdefault/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $module = \DB::for_table('module')
                ->where_equal('module_id' , $id)
                ->find_one();

            if ( $module->module_default == 1 ) {
                $module->module_default = 0;
                $module->save();

                \App\Kernel\Back\Log::getInstance()->warning( 39 , $module->module_name ) ;

                $msg = "Le module n'est plus le principal";
                $ret = true;
            }
            else {
                $msg = "Impossible, le module n'est pas le principal";
                $ret = false;
            }
        }
        else {
            $msg = "Le token de sécurité est invalide";
            $ret = false;
        }

        $app->flash('__msg',addslashes( json_encode( $msg )));
        $app->flash('__result',$ret);
        $app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
    })->name('moduleadmin_notdefault');
	
	$app->get('/disactive/:id/:token', function ($id,$token) use ($app)
	{
		if ( $token == $_SESSION[ $app->config('token') ] ) {
			$module = \DB::for_table('module')
							->where_equal('module_id' , $id)
							->find_one();
			
			if ( $module->module_active == 1 ) {
				$module->module_active = 0;
				$module->save();
				
				\App\Kernel\Back\Log::getInstance()->warning( 19 , $module->module_name ) ;
						
				$msg = "Le module a bien été désactivé";
				$ret = true;
			}
			else {
				$msg = "Impossible, le module est déja désactivé";
				$ret = false;
			}
		}
		else {
			$msg = "Le token de sécurité est invalide";
			$ret = false;
		}
		
		$app->flash('__msg',addslashes( json_encode( $msg )));
		$app->flash('__result',$ret);
		$app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
	})->name('moduleadmin_disactive');
	
	$app->map('/edit(/:id)', function ($id = -1) use ($app)
	{
		$error 	  = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('module')
			->where_equal('module_id' , $id)
			->find_one();
		
		if ( $id != -1 && !$contentRow ) {
			$app->redirect( $app->config('admin.url') . '/admin/moduleadmin');
		}
		
		if ( $app->request->isPost() ) {
			$post = array(
				"module_name" => $app->request->post('module_name'),
				"module_class_name" => $app->request->post('module_class_name'),
				"module_icon" => $app->request->post('module_icon'),
				"module_active" => $app->request->post('module_active')
			) ;
			
			if ( !$contentRow ) {
				$contentRow = DB::for_table('module')->create();
				$add = true ;
			}
			
			if ( $app->request->post('module_name') == "" ) {
					$error = true ;
					$tabError['module_name'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $app->request->post('module_class_name') == "" ) {
					$error = true ;
					$tabError['module_class_name'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $app->request->post('module_icon') == "" ) {
					$error = true ;
					$tabError['module_icon'] = "Veuillez remplir ce champ" ;
			}
			
			if ( $error == false ) {
				$contentRow->module_name 		= $app->request->post('module_name') ;
				$contentRow->module_class_name 	= $app->request->post('module_class_name') ;
				$contentRow->module_icon 		= $app->request->post('module_icon') ;
				
				$contentRow->module_active 		= ( $app->request->post('module_active') == NULL ? 0 : 1 ) ;
				$contentRow->save() ;
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 21 : 17 ) , $contentRow->module_name ) ;
				
				$id = $contentRow->module_id;
				
				$app->flash('__msg',addslashes( json_encode( "Le module a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
				$app->flash('__result',true);
				
				if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/admin/moduleadmin/edit/' . $id );
				else 											$app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
			}
		}
		else {
			$post = $contentRow ;
		}
		
		$app->render('admin/moduleadmin/edit.twig.html', array( 
			"post" => $post,
			"id" => $id,
			"error"		 => ( $error === false ? "0" : "1" ),
			"tabError"	 => json_encode( $tabError )
		));
	})->name('moduleadmin_edit')->via('GET', 'POST');
});