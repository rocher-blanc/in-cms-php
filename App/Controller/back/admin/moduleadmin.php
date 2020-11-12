<?php

use App\Kernel\Container;
use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/moduleadmin', function () use ($app)
	{
        $app->get('/icon/:field', function ( $field ) use ($app)
        {
            $app->render('admin/moduleadmin/icon.twig', [
                "field" => $field
            ]);
        });

        $app->post('/generate', function () use ($app)
        {
            $g = json_decode( $_POST['global'] , true );
            $f = json_decode( $_POST['fields'] , true );

//            dump($f);

            $php = '' ;
            $php.= "<"."?"."php\n\n" ;
            $php.= "namespace Project\Module\Entity;\n\n" ;
            $php.= "use App\Kernel\Entity\Builder;\n\n" ;
            $php.= "/* Module généré automatiquement par le générateur de easyDOOR le " . date('d/m/Y H:i:s') . " */\n\n" ;
            $php.= "class " . $g['name'] . " extends Builder\n" ;
            $php.= "{\n" ;
            $php.= "\tprotected function load()\n" ;
            $php.= "\t{\n" ;

                if ( $g['isDepedency'] ) $php.= "\t\t$"."this->isDependency();\n" ;
                if ( $g['order'] ) $php.= "\t\t$"."this->enableOrder();\n" ;
                if ( $g['limit'] !== false ) $php.= "\t\t$"."this->setMaxElement(".$g['limit'].");\n" ;
                if ( $g['pagination'] !== false ) $php.= "\t\t$"."this->setPagination(".$g['pagination'].");\n" ;
                if ( $g['user'] ) $php.= "\t\t$"."this->enableUser();\n" ;
                if ( $g['user_module'] ) $php.= "\t\t$"."this->enableUserModule();\n" ;
                if ( $g['edition']['delete'] === false ) $php.= "\t\t$"."this->disableDelete();\n" ;
                if ( $g['edition']['edit'] === false ) $php.= "\t\t$"."this->disableUpdate();\n" ;
                if ( $g['edition']['add'] === false ) $php.= "\t\t$"."this->disableCreate();\n" ;
                if ( $g['edition']['import'] === false ) $php.= "\t\t$"."this->disableImport();\n" ;
                if ( $g['edition']['valid'] ) $php.= "\t\t$"."this->enableValidation();\n" ;

            $php.= "\t\t\n" ;

                if ( ! empty( $f ) )
                {
                    foreach ( $f as $field )
                    {
                        $php.= "\t\t$"."this->build('".$field['id']."')\n" ;
                        $php.= "\t\t\t->name(\"".$field['name']."\")\n" ;
                        $php.= "\t\t\t->column(".$field['column']['size'].",".$field['column']['total'].")\n" ;
                        $php.= "\t\t\t->name(\"".$field['name']."\")\n" ;

                        if ( $field['lang'] ) $php.= "\t\t\t->isLang()\n" ;
                        if ( $field['comment'] !== false ) $php.= "\t\t\t->comment(\"". $field['comment'] ."\")\n" ;
                        if ( $field['isUrl'] ) $php.= "\t\t\t->isUrl()\n" ;
                        if ( $field['empty'] ) $php.= "\t\t\t->notEmpty(\"Merci de remplir le champ : ".$field['name']."\")\n" ;
                        if ( $field['textareaHtml'] ) $php.= "\t\t\t->editor()\n" ;
                        if ( $field['type'] == 'input' ) $php.= "\t\t\t->isVarchar()\n" ;
                        if ( $field['type'] == 'textarea' ) $php.= "\t\t\t->isText()\n" ;
                        if ( $field['type'] == 'gallery' ) $php.= "\t\t\t->isGallery()\n" ;
                        if ( $field['type'] == 'document' ) $php.= "\t\t\t->isDocument()\n" ;
                        if ( $field['type'] == 'video' ) $php.= "\t\t\t->isVideo()\n" ;
                        if ( $field['type'] == 'link' ) $php.= "\t\t\t->isLink()\n" ;
                        if ( $field['type'] == 'image' ) $php.= "\t\t\t->isImage()\n" ;
//                        if ( $field['type'] == 'radio' ) $php.= "\t\t\t->isRadio()\n" ;
                        if ( $field['type'] == 'boolean' ) $php.= "\t\t\t->isBoolean()\n" ;
                        if ( $field['type'] == 'checkbox' ) $php.= "\t\t\t->isCheckbox()\n" ;
                        if ( $field['type'] == 'integer' ) $php.= "\t\t\t->isInteger()\n" ;
                        if ( $field['type'] == 'float' ) $php.= "\t\t\t->isFloat()\n" ;
                        if ( $field['type'] == 'icon' ) $php.= "\t\t\t->isIcon()\n" ;
                        if ( $field['type'] == 'password' ) $php.= "\t\t\t->isPassword()\n" ;
                        if ( $field['type'] == 'hour' ) $php.= "\t\t\t->isHour()\n" ;

                        if ( $field['type'] == 'date' )
                        {
                            if ( $field['dateWithHour'] )   $php.= "\t\t\t->isDate(true)\n" ;
                            else                            $php.= "\t\t\t->isDate()\n" ;

                            if ( ! empty( $field['selectOptions'] ) )
                            {
                                foreach ( $field['selectOptions'] as $option )
                                {
                                    $php.= "\t\t\t->format(\"".$option['value']."\" , \"".$option['value']."\")\n" ;
                                }
                            }
                        }

                        if ( $field['type'] == 'select' )
                        {
                            $php.= "\t\t\t->isSelect()\n" ;
                            if ( $field['many'] !== false )
                            {
                                $php.= "\t\t\t->manyToMany(\"". $field['many'] ."\")\n" ;
                            }
                            else
                            {
                                if ( ! empty( $field['selectOptions'] ) )
                                {
                                    $php.= "\t\t\t->option([\n" ;
                                    foreach ( $field['selectOptions'] as $option )
                                    {
                                        $php.= "\t\t\t\t\"".$option['value']."\" => \"".$option['value']."\",\n" ;
                                    }
                                    $php.= "\t\t\t])\n" ;
                                }
                            }
                        }

                        $php.= "\t\t\t;\n\n" ;
                    }
                }



            $php.= "\t}\n" ;
            $php.= "}" ;

            echo '<pre>';
            dump( $php );

        });

        $app->get('/new', function () use ($app)
        {
            $contentRows = \DB::for_table('module')
                ->where_equal('module_kernel' , 0)
                ->order_by_asc('module_active')
                ->order_by_asc('module_name')
                ->find_many();

            $tab = [];
            $dep = [];

            if ( $contentRows )
            {
                foreach( $contentRows as $row )
                {
                    $entity = Container::getInstance()->module( $row->module_class_name )->getEntity();

                    if ( $entity->itsDepedency() )
                    {
                        $dep[] = [
                            'class' => $row->module_class_name,
                            'name' => $row->module_class_name
                        ];
                    }
                    else
                    {
                        $tab[] = [
                            'class' => $row->module_class_name,
                            'name' => $row->module_class_name
                        ];
                    }
                }
            }

            $app->render('admin/moduleadmin/new.twig', [
                "depedency" => $dep,
                "module" => $tab
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
					$entity = Container::getInstance()->module( $row->module_class_name )->getEntity();
					if ( $entity )
                    {
                        $img[ $row->module_id ] = $entity->hasImage();
                    }
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
						/*$entity = Container::getInstance()->module( $name )->getEntity();
						$entity->hasImage();*/

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
				if ( ! file_exists( WEBSERVICE_PROJECT_PATH . "/" . $name . ".php" ) ) Factory::getInstance()->File()->create( WEBSERVICE_PROJECT_PATH . "/" . $name . ".php" , $php );


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
					if ( ! file_exists( REPOSITORY_PROJECT_PATH . "/" . $row . "/" . $name . ".php" ) ) Factory::getInstance()->File()->create( REPOSITORY_PROJECT_PATH . "/" . $row . "/" . $name . ".php" , $php );
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
					if ( ! file_exists( MODULE_PATH . "/Controller/" . $row . "/" . $name . ".php" ) ) Factory::getInstance()->File()->create( MODULE_PATH . "/Controller/" . $row . "/" . $name . ".php" , $php );
				}

                Container::getInstance()->module( $name )->getRepository( true )->checkDatabase();
				Container::getInstance()->param()->set('key_module_' . $contentRow->module_id , md5_file( ENTITY_PATH . "/" . $contentRow->module_class_name . ".php" ) );
				Factory::getInstance()->Response()->flashAndRedirect( Translate::getInstance()->getText( 'msg_module_installed' ) , true , '/admin/moduleadmin' );

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
				'url' => Factory::getInstance()->Url()->get('admin/moduleadmin/image/' . $id )
			]);
		});

		$app->post('/image/:id', function ($id) use ($app)
		{
			set_time_limit(0);
			$ret = false;
			$contentRow = \DB::for_table('module')
				->where_equal('module_id', $id)
				->find_one();

			if ( $contentRow )
			{
				$entity = Container::getInstance()->module($contentRow->module_class_name)->getEntity();
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

										$Media->genImages( $field );
									}
								}
							}
						}
					}

					\App\Kernel\Back\Log::getInstance()->warning( 47 , $contentRow->module_name ) ;

					$ret = true ;
					$msg = Translate::getInstance()->getText( 'msg_img_regeneree') ;
				}
			}
			else
			{
				$msg = Translate::getInstance()->getText( 'msg_pb_technique') ;
			}

			$result['msg'] = $msg;
			$result['result'] = $ret;
			$result['url'] = Factory::getInstance()->Url()->get('/admin/moduleadmin') ;

			Factory::getInstance()->Response()->printJSON($result) ;
		});

		$app->get('/delete/:id', function ($id) use ($app)
		{
			$app->render('admin/moduleadmin/delete.twig',[
				'id' => $id,
				'url' => Factory::getInstance()->Url()->get('admin/moduleadmin/delete/' . $id )
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

				$msg = Translate::getInstance()->getText( 'msg_module_uninstalled') ;
				$ret = true ;
				$contentRow->delete();
			}
			else
			{
				$msg = Translate::getInstance()->getText( 'msg_module_uninstalled_error') ;
			}

			$result['msg'] = $msg;
			$result['result'] = $ret;
			$result['url'] = Factory::getInstance()->Url()->get('/admin/moduleadmin') ;

			Factory::getInstance()->Response()->printJSON($result) ;

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

			$result['msg'] = Translate::getInstance()->getText( msg_module_emptied);
			$result['result'] = true;
			$result['url'] = Factory::getInstance()->Url()->get('/admin/moduleadmin') ;

			Factory::getInstance()->Response()->printJSON($result) ;
		})->name('moduleadmin_truncate');

		$app->get('/truncate/:id', function ($id) use ($app)
		{
			$app->render('admin/moduleadmin/truncate.twig',[
				'id' => $id,
				'url' => Factory::getInstance()->Url()->get('admin/moduleadmin/truncate/' . $id )
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

					$msg = Translate::getInstance()->getText( 'msg_module_active');
					$ret = true;
				}
				else {
					$msg = Translate::getInstance()->getText( 'msg_module_active_error');
					$ret = false;
				}
			}
			else {
				$msg = Translate::getInstance()->getText( 'msg_token_invalider');
				$ret = false;
			}

			$app->flash('__msg',addslashes( json_encode( $msg )));
			$app->flash('__result',$ret);
			$app->redirect( $app->config('admin.url') . '/admin/moduleadmin' );
			Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
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

						$msg = Translate::getInstance()->getText( 'msg_main_module');
						$ret = true;
					}
					else {
						$msg = Translate::getInstance()->getText( 'msg_main_module_error');
						$ret = false;
					}
				}
				else {
					$msg = Translate::getInstance()->getText( 'msg_main_module_error2');
					$ret = false;
				}
			}
			else {
				$msg = Translate::getInstance()->getText( 'msg_token_invalide');
				$ret = false;
			}

			Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
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

					$msg = Translate::getInstance()->getText( 'msg_secondary_module');
					$ret = true;
				}
				else {
					$msg = Translate::getInstance()->getText( 'msg_secondary_module_error');
					$ret = false;
				}
			}
			else {
				$msg = Translate::getInstance()->getText( 'msg_token_invalide');
				$ret = false;
			}

			Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
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

					$msg = Translate::getInstance()->getText( 'msg_module_desactive');
					$ret = true;
				}
				else {
					$msg = Translate::getInstance()->getText( 'msg_module_desactive_error');
					$ret = false;
				}
			}
			else {
				$msg = Translate::getInstance()->getText( 'msg_token_invalide');
				$ret = false;
			}


			Factory::getInstance()->Response()->flashAndRedirect($msg , $ret , '/admin/moduleadmin' );
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
				$result['msg'] = Translate::getInstance()->getText( 'msg_module_inexistant') ;
			}

			if ( !$contentRow ) {
				$contentRow = DB::for_table('module')->create();
				$add = true ;
			}

			if ( $app->request->post('module_name') == "" ) {
				$result['msg'] = Translate::getInstance()->getText( 'mandatory_module_name') ;
				$result['field'] = 'module_name' ;
			}
			else if ( $app->request->post('module_class_name') == "" ) {
				$result['msg'] = Translate::getInstance()->getText( 'mandatory_class_name') ;
				$result['field'] = 'module_class_name' ;
			}
			else if ( $app->request->post('module_icon') == "" ) {
				$result['msg'] = Translate::getInstance()->getText( 'mandatory_icon') ;
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
				$result['url'] = Factory::getInstance()->Url()->get('/admin/moduleadmin') ;
				$result['msg'] = "Le module a bien été " . ( $add == true ? "ajouté" : "modifié" ) ;
			}

			Factory::getInstance()->Response()->printJSON($result) ;
		});
	});