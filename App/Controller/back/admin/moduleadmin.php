<?php

	$app->group('/moduleadmin', function () use ($app)
	{
        $app->get('/icon/:field', function ( $field ) use ($app)
        {
            $app->render('admin/moduleadmin/icon.twig', [
                "field" => $field
            ]);
        });

        $app->get('/', function () use ($app)
        {
            $contentRows = \DB::for_table('module')
                ->where_equal('module_kernel' , 0)
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

			$img = [];

			if ( $contentRows )
			{
				foreach( $contentRows as $row )
				{
					$entity = \App\Kernel\Container::getInstance()->module( $row->module_class_name )->getEntity();
					$img[ $row->module_id ] = $entity->hasImage();
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

					if ( ! array_key_exists( $name , $tab ) )
					{
						$entity = \App\Kernel\Container::getInstance()->module( $name )->getEntity();
						$entity->hasImage();

						$list[] = $name ;
					}
				}
			}

			$app->render('admin/moduleadmin/index.twig.html', [
				"img" => $img,
				"contentRows" => $contentRows,
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
				$contentRow->module_icon 		= "icon-question" ;
				$contentRow->module_active 		= 1 ;
				$contentRow->save() ;

				// On génère le webservice
				$php = '' ;
				$php.= "<"."?"."php\n\n" ;
				$php.= "namespace Project\Module\Webservice;\n\n" ;
				$php.= "class " . $name . " extends \App\Kernel\Front\WebserviceModule\n" ;
				$php.= "{\n" ;
				$php.= "\t\n" ;
				$php.= "}" ;
				if ( ! file_exists( WEBSERVICE_PROJECT_PATH . "/" . $name . ".php" ) ) \App\Kernel\Factory::getInstance()->File()->create( WEBSERVICE_PROJECT_PATH . "/" . $name . ".php" , $php );


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

                \App\Kernel\Container::getInstance()->module( $name )->getRepository( true )->checkDatabase();
				\App\Kernel\Container::getInstance()->param()->set('key_module_' . $contentRow->module_id , md5_file( ENTITY_PATH . "/" . $contentRow->module_class_name . ".php" ) );
				\App\Kernel\Factory::getInstance()->Response()->flashAndRedirect("Le module a bien été installé" , true , '/admin/moduleadmin' );
			}
			else
			{
				$app->redirect( $app->config('admin.url') . '/admin/moduleadmin/edit/' . $contentRow->module_id );
			}

		})->name('moduleadmin_install');

		//generateImage
		$app->get('/image/:id', function ($id) use ($app)
		{
			$app->render('admin/moduleadmin/generateImage.twig',[
				'id' => $id,
				'url' => \App\Kernel\Factory::getInstance()->Url()->get('admin/moduleadmin/image/' . $id )
			]);
		});

		$app->post('/image/:id', function ($id) use ($app)
		{
			$ret = false;
			$contentRow = \DB::for_table('module')
				->where_equal('module_id', $id)
				->find_one();

			if ( $contentRow )
			{
				$entity = \App\Kernel\Container::getInstance()->module($contentRow->module_class_name)->getEntity();
				if ( $entity->hasImage() )
				{
					foreach( $entity->getField() as $field )
					{
						if ( $field->getType() == 'image' )
						{
							$content = \DB::for_module( $contentRow->module_class_name )
								->select( $field->getColumn() , 'value' )
								->find_many();

							if ( $content )
							{
								foreach( $content as $row )
								{
									if ( $row->get('value') != 0 )
									{
										$Media = new \App\Kernel\Back\Media;
										$Media->setModuleId( $contentRow->module_id ) ;
										$Media->setImageId( $row->get('value') );
										$Media->setFolder( $entity->getFolder() ) ;
										$Media->getNameById() ;
										$Media->genThumb( 100 , 100 ) ;

										if ( $field->hasThumb() )
										{
											foreach( $field->getThumb() as $thumb )
											{
												// width, height
												$Media->genThumb( $thumb[0] , $thumb[1] ) ;
											}
										}

										if ( $field->hasCrop() )
										{
											foreach( $field->getCrop() as $crop )
											{
												// width, height
												$Media->genThumb( $crop[0] , $crop[1] , true ) ;
											}
										}
									}
								}
							}
						}
					}

					\App\Kernel\Back\Log::getInstance()->warning( 47 , $contentRow->module_name ) ;

					$ret = true ;
					$msg = "Les images ont bien été regénéré" ;
				}
			}
			else
			{
				$msg = "Problème technique lors de l'opération" ;
			}

			$result['msg'] = $msg;
			$result['result'] = $ret;
			$result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/admin/moduleadmin') ;

			\App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
		});

		$app->get('/delete/:id', function ($id) use ($app)
		{
			$app->render('admin/moduleadmin/delete.twig',[
				'id' => $id,
				'url' => \App\Kernel\Factory::getInstance()->Url()->get('admin/moduleadmin/delete/' . $id )
			]);
		});

		$app->post('/delete/:id', function ($id) use ($app)
		{
			$ret = false ;
			$contentRow = \DB::for_table('module')
				->where_equal('module_id' , $id)
				->find_one();

			if ( $contentRow )
			{
				\App\Kernel\Back\Log::getInstance()->warning( 20 , $contentRow->module_name ) ;

				$msg = "Le module a bien été désinstallé" ;
				$ret = true ;
				$contentRow->delete();
			}
			else
			{
				$msg = "Une erreur est survenue lors de la désinstallation" ;
			}

			$result['msg'] = $msg;
			$result['result'] = $ret;
			$result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/admin/moduleadmin') ;

			\App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;

		})->name('moduleadmin_delete');

		$app->post('/truncate/:id', function ($id) use ($app)
		{
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

			$result['msg'] = "Le module a bien été vidé";
			$result['result'] = true;
			$result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/admin/moduleadmin') ;

			\App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
		})->name('moduleadmin_truncate');

		$app->get('/truncate/:id', function ($id) use ($app)
		{
			$app->render('admin/moduleadmin/truncate.twig',[
				'id' => $id,
				'url' => \App\Kernel\Factory::getInstance()->Url()->get('admin/moduleadmin/truncate/' . $id )
			]);
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
			\App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
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

			\App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
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

			\App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
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


			\App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
		})->name('moduleadmin_disactive');

		$app->get('/edit(/:id)', function ($id = -1) use ($app)
		{
			$contentRow = \DB::for_table('module')
				->where_equal('module_id' , $id)
				->find_one();

			if ( $id != -1 && !$contentRow ) {
				$app->redirect( $app->config('admin.url') . '/admin/moduleadmin');
			}

			$app->render('admin/moduleadmin/edit.twig.html', array(
				"post" => $contentRow,
				"id" => $id
			));
		});

		$app->post('/edit(/:id)', function ($id = -1) use ($app)
		{
			$result['result'] = false ;
			$contentRow = NULL ;

			if ( $id != -1 )
			{
				$contentRow = \DB::for_table('module')
					->where_equal('module_id' , $id)
					->find_one();
			}

			if ( $id != -1 && !$contentRow ) {
				$result['msg'] = 'Ce module n\'éxiste pas!' ;
			}

			if ( !$contentRow ) {
				$contentRow = DB::for_table('module')->create();
				$add = true ;
			}

			if ( $app->request->post('module_name') == "" ) {
				$result['msg'] = "Veuillez indiquer le nom du module" ;
				$result['field'] = 'module_name' ;
			}
			else if ( $app->request->post('module_class_name') == "" ) {
				$result['msg'] = "Veuillez indiquer le nom de la class" ;
				$result['field'] = 'module_class_name' ;
			}
			else if ( $app->request->post('module_icon') == "" ) {
				$result['msg'] = "Veuillez choisir une icône" ;
				$result['field'] = 'module_icon' ;
			}
			else
			{
				$contentRow->module_name 		= $app->request->post('module_name') ;
				$contentRow->module_class_name 	= $app->request->post('module_class_name') ;
				$contentRow->module_icon 		= $app->request->post('module_icon') ;
				$contentRow->module_active 		= $app->request->post('module_active') ;
				$contentRow->save() ;

				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 21 : 17 ) , $contentRow->module_name ) ;

				$id = $contentRow->module_id;
				$result['result'] = true ;
				$result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/admin/moduleadmin') ;
				$result['msg'] = "Le module a bien été " . ( $add == true ? "ajouté" : "modifié" ) ;
			}

			\App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
		});
	});