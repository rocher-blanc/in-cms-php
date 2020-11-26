<?php

use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/langue', function () use ($app)
{
    $app->get('/', function () use ($app) {

        $contentRows = \DB::for_table('lang')
            ->order_by_desc('lang_status')
            ->find_many();

        $app->render('ext/langue/index.twig.html', array( "contentRows" => $contentRows ));

    })->name('langue_index');


    $app->post('/import', function () use ($app) {
        $upload_dir 	= UPLOAD_PATH . '/' ;
        $upload_url 	= str_replace( WEB_PATH , '' , $upload_dir ) ;
        $upload_handler = new \App\Kernel\Back\Upload([
            'upload_dir' => $upload_dir,
            'param_name' => 'files'
        ], true, null, function( $file ) use ($app) {
            $objPHPExcel = \PHPExcel_IOFactory::load( UPLOAD_PATH . '/' . $file );
            $sheetData   = $objPHPExcel->getActiveSheet()->toArray(NULL, FALSE, FALSE, TRUE);

            $arrayLang = [];
            $arrayTrad = [];
            $arrayKey = [];

            if ( $sheetData )
            {
                foreach( $sheetData as $numberLine => $line )
                {
                    // info langue
                    if ( $numberLine == 1 )
                    {
                        foreach( $line as $info => $cell )
                        {
                            if ( $info != "A" )
                            {
                                list( $name , $url ) = explode( '/' , $cell ) ;
                                $arrayLang[ $info ] = trim( $url ) ;
                            }
                        }
                    }
                    else
                    {
                        foreach( $line as $info => $cell )
                        {
                            $lang = $arrayLang[ $info ] ;
                            if ( $info == "A" )
                            {
                                $arrayKey[ $numberLine ] = $cell ;
                            }
                            else
                            {
                                $cle = $arrayKey[ $numberLine ] ;
                                $arrayTrad[ $lang ][ $cle ] = $cell ;
                            }
                        }
                    }
                }
            }

            foreach( $arrayTrad as $lang => $row )
            {
                $src = "<"."?php\n";
                $src.= "namespace Project\Lang;\n";
                $src.= "class " . strtoupper( $lang ) . " extends \App\Kernel\Front\LanguageModel {\n";
                $src.= "\tprotected $"."a = [\n";

                foreach( $row as $cle => $value )
                {
                    $txt = htmlentities( trim( $value ) );

                    if ( ! empty( $cle ) ) $src.= "\t\t\"" . trim( $cle ) . "\" => \"" . $txt . "\",\n";
                }

                $src.= "\t];\n";
                $src.= "}\n";

                if ( ! empty( $lang ) ) Factory::getInstance()->File()->create( LANG_PATH . "/" . strtoupper( $lang ) . ".php" , $src );
            }

            $login = strtolower( $_SESSION[ $app->config('session') ]['username'] ) ;
            $login = str_replace( ' ' , '' , $login ) ;
            $date  = date('Y-m-d--H-i-s') ;
            $exp   = explode( "." , $file ) ;
            $ext   = end( $exp ) ;

            rename( UPLOAD_PATH . '/' . $file , TRAD_PATH . "/" . $date . "-" . $login . "-import." . $ext ) ;

            Factory::getInstance()->Response()->returnJSON( "Les traductions ont bien été importées" , true );
        });
    });


    $app->map('/edit/:id', function ($id) use ($app)
    {
        $error 	  = false ;
        $tabError = array() ;

        $contentRows = \DB::for_table('lang')
            ->where_equal('lang_id',$id)
            ->find_one();

        if ( ! $contentRows ) {
            $app->redirect( $app->config('admin.url') . '/langue' );
        }
        else {
            if ( $app->request->isPost() ) {
                if ( $app->request->post('lang_display') == "" ) {
                    $error = true ;
                    $tabError['lang_display'] = Translate::getInstance()->getText( 'mandatory_fillin' );
                }
                else {
                    $contentRows->lang_display = $app->request->post('lang_display') ;
                    $contentRows->save() ;

                    \App\Kernel\Back\Log::getInstance()->info( 14 , $contentRows->lang_display ) ;

                    $id = $contentRows->lang_id;

                    $app->flash('__msg',addslashes( json_encode( "La langue a bien été modifiée" ) ) );
                    $app->flash('__result',true);

                    if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/langue/edit/' . $id );
                    else 											$app->redirect( $app->config('admin.url') . '/ext/langue' );
                }
            }
        }

        $app->render('ext/langue/edit.twig.html', array(
            "post" => $contentRows,
            "id" => $id,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )
        ));
    })->name('langue_edit')->via('GET', 'POST');

    $app->group('/order', function () use ($app)
    {
        $app->get('/up/:id/:token', function ($id,$token) use ($app)
        {
            if ( $token == $_SESSION[ $app->config('token') ] ) {
                $lang = \DB::for_table('lang')
                    ->where_equal('lang_id' , $id)
                    ->find_one();

                // On check qu'elle n est pas a 0 en status
                if ( $lang->lang_status > 0 ) {
                    $first = \DB::for_table('lang')
                        ->where_gt('lang_status' , $lang->lang_status)
                        ->count();

                    // On check qu elle n est pas deja la premiere
                    if ( $first != 0 ) {
                        $sup = $lang->lang_status + 1;
                        $langSup = \DB::for_table('lang')
                            ->where_equal('lang_status' , $sup)
                            ->find_one();
                        $langSup->lang_status = $lang->lang_status;
                        $langSup->save();

                        $lang->lang_status = $sup;
                        if ( $first == 1 ) $lang->lang_front = 1;
                        $lang->save();

                        \App\Kernel\Back\Log::getInstance()->warning( 15 , $lang->lang_display ) ;

                        $msg = Translate::getInstance()->getText('pos_change_lang' );
                        $ret = true;
                    }
                    else {
                        $msg = Translate::getInstance()->getText('pos_err_lang' );
                        $ret = false;
                    }
                }
                else {
                    $msg = Translate::getInstance()->getText('pos_inactive_lang' );
                    $ret = false;
                }
            }
            else {
                $msg = Translate::getInstance()->getText('msg_token_invalide' );
                $ret = false;
            }

            Factory::getInstance()->Response()->flashAndRedirect( $msg , $ret , '/ext/langue' );
        })->name('langue_up');

        $app->get('/down/:id/:token', function ($id,$token) use ($app)
        {
            if ( $token == $_SESSION[ $app->config('token') ] ) {
                $lang = \DB::for_table('lang')
                    ->where_equal('lang_id' , $id)
                    ->find_one();

                // On check qu'elle n est pas a 0 en status
                if ( $lang->lang_status > 0 ) {
                    if ( $lang->lang_status > 1 ) {
                        $sup = $lang->lang_status - 1;
                        $langSup = \DB::for_table('lang')
                            ->where_equal('lang_status' , $sup)
                            ->find_one();

                        $langSup->lang_status = $lang->lang_status;
                        $langSup->save();

                        $lang->lang_status = $sup;
                        $lang->save();

                        \App\Kernel\Back\Log::getInstance()->warning( 16 , $lang->lang_display ) ;

                        $msg = Translate::getInstance()->getText( 'pos_change_lang' );
                        $ret = true;
                    }
                    else {
                        $msg = Translate::getInstance()->getText('pos_err_bas_lang' );
                        $ret = false;
                    }
                }
                else {
                    $msg = Translate::getInstance()->getText('pos_inactive_lang' );
                    $ret = false;

                }
            }
            else {
                $msg = Translate::getInstance()->getText('msg_token_invalide' );
                $ret = false;
            }

            Factory::getInstance()->Response()->flashAndRedirect( $msg , $ret , '/ext/langue' );
        })->name('langue_down');

    });

    $app->group('/front', function () use ($app)
    {
        $app->get('/active/:id/:token', function ($id,$token) use ($app)
        {
            if ( $token == $_SESSION[ $app->config('token') ] ) {
                $lang = \DB::for_table('lang')
                    ->where_equal('lang_id' , $id)
                    ->find_one();

                if ( $lang->lang_status != 0 )
                {
                    $lang->lang_front  = 1;
                    $lang->save();

                    \App\Kernel\Back\Log::getInstance()->warning( 42 , $lang->lang_display ) ;

                    $msg = Translate::getInstance()->getText( 'langue_available_site' );
                    $ret = true;
                }
                else {
                    $msg = Translate::getInstance()->getText( 'pos_inactive_lang' );
                    $ret = false;
                }
            }
            else {
                $msg = Translate::getInstance()->getText( 'msg_token_invalide' );
                $ret = false;
            }

            Factory::getInstance()->Response()->flashAndRedirect( $msg , $ret , '/ext/langue' );
        })->name('langue_active');

        $app->get('/disactive/:id/:token', function ($id,$token) use ($app)
        {
            if ( $token == $_SESSION[ $app->config('token') ] ) {
                $lang = \DB::for_table('lang')
                    ->where_equal('lang_id' , $id)
                    ->find_one();

                if ( $lang->lang_status > 0 )
                {
                    $first = \DB::for_table('lang')
                        ->where_gt('lang_status' , $lang->lang_status)
                        ->count();

                    // On check qu elle n est pas deja la premiere
                    if ( $first != 0 )
                    {
                        $lang->lang_front  = 0;
                        $lang->save();

                        \App\Kernel\Back\Log::getInstance()->warning( 43 , $lang->lang_display ) ;

                        $msg = Translate::getInstance()->getText( 'langue_unavailable_site' );
                        $ret = true;
                    }
                    else
                    {
                        $msg = Translate::getInstance()->getText( 'langue_default_disable_err' );
                        $ret = false;
                    }
                }
                else
                {
                    $msg = Translate::getInstance()->getText( 'langue_disable_err' );
                    $ret = false;
                }
            }
            else
            {
                $msg = Translate::getInstance()->getText( 'msg_token_invalide' );
                $ret = false;
            }

            Factory::getInstance()->Response()->flashAndRedirect( $msg , $ret , '/ext/langue' );
        })->name('langue_disactive');
    });

    $app->get('/active/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $lang = \DB::for_table('lang')
                ->where_equal('lang_id' , $id)
                ->find_one();

            if ( $lang->lang_status == 0 ) {
                $langs = \DB::for_table('lang')
                    ->where_not_equal('lang_status' , 0)
                    ->find_many();

                foreach( $langs as $row ) {
                    $row->lang_status += 1;
                    $row->save();
                }

                $lang->lang_status = 1;
                $lang->lang_front  = 0;
                $lang->save();

                \App\Kernel\Back\Log::getInstance()->warning( 12 , $lang->lang_display ) ;

                $msg = Translate::getInstance()->getText( 'langue_activated' );
                $ret = true;
            }
            else {
                $msg = Translate::getInstance()->getText( 'lang_already_activated_err' );
                $ret = false;
            }
        }
        else {
            $msg = Translate::getInstance()->getText( 'msg_token_invalide' );
            $ret = false;
        }

        Factory::getInstance()->Response()->flashAndRedirect( $msg , $ret , '/ext/langue' );
    })->name('langue_active');

    $app->get('/disactive/:id/:token', function ($id,$token) use ($app)
    {
        if ( $token == $_SESSION[ $app->config('token') ] ) {
            $count = \DB::for_table('lang')
                ->where_not_equal('lang_status' , 0)
                ->count();

            if ( $count > 1 ) {
                $lang = \DB::for_table('lang')
                    ->where_equal('lang_id' , $id)
                    ->find_one();

                if ( $lang->lang_status > 0 ) {
                    $langs = \DB::for_table('lang')
                        ->where_not_equal('lang_status' , 0)
                        ->where_gt('lang_status' , $lang->lang_status)
                        ->find_many();

                    foreach( $langs as $row ) {
                        $row->lang_status -= 1;
                        $row->save();
                    }

                    $lang->lang_status = 0;
                    $lang->lang_front  = 0;
                    $lang->save();

                    $defaut = \DB::for_table('lang')
                        ->limit(1)
                        ->order_by_desc('lang_status')
                        ->find_one();
                    $defaut->lang_front  = 1;
                    $defaut->save();

                    \App\Kernel\Back\Log::getInstance()->warning( 13 , $lang->lang_display ) ;

                    $msg = Translate::getInstance()->getText('langue_disabled_info' );
                    $ret = true;
                }
                else {
                    $msg = Translate::getInstance()->getText('pos_change_lang' );
                    $ret = false;
                }
            }
            else {
                $msg = Translate::getInstance()->getText( 'langue_requirement_err' );
                $ret = false;
            }
        }
        else {
            $msg = Translate::getInstance()->getText( 'msg_token_invalide' );
            $ret = false;
        }

        Factory::getInstance()->Response()->flashAndRedirect( $msg , $ret , '/ext/langue' );
    })->name('langue_disactive');


    $app->get('/traduction', function () use ($app) {

        $contentRows = \App\Kernel\Lang::getInstance()->getAll();

        $app->render('ext/langue/traduction.twig.html', [
            "contentRows" => $contentRows
        ]);

    })->name('langue_traduction');

    $app->get('/traduction/get-lang', function() use ($app) {
        header('Content-Type: application/json;charset=utf-8');

        $lang_abbr = $this->getApp()->request->get('lang_locale');
        $className = "\Project\Lang\\" . strtoupper( $lang_abbr ) ;
        $class     = new $className ;
        $arrayTrad = $class->getVar();

        ksort( $arrayTrad );
        $keys = [];

        foreach( $arrayTrad as $key => $value ) {
            if( !empty($key) )
            {
                $keys[$key] = [
                    'type'  => "text", //html
                    'value' => $value
                ];
            }
        }

        $req = \DB::for_table("lang")
			->where_equal("lang_url", $lang_abbr)
			->find_one();

		$lang = [
			'id' => $req ? $req->lang_id : 0,
			'locale' => $lang_abbr,
			'title' => $req ? $req->lang_display : "Langue"
		];

        echo json_encode([
            'result' => true,
            'msg'    => "",
            'lang'   => $lang,
            'keys'   => $keys,
        ]);
    });

    $app->post('/traduction/update-translate', function() use ($app) {
        $key   = $app->request->post('key');
        $lang  = $this->getApp()->request->post('lang');
        $type  = $app->request->post('type');
        $value = $app->request->post('value');

        $className = "\Project\Lang\\" . strtoupper( $lang ) ;
        $class     = new $className ;
        $arrayTrad = $class->getVar();

        ksort( $arrayTrad );

        $arrayTrad[$key] = $value;

        $src = "<"."?php\n";
        $src.= "namespace Project\Lang;\n";
        $src.= "class " . strtoupper( $lang ) . " extends \App\Kernel\Front\LanguageModel {\n";
        $src.= "\tprotected $"."a = [\n";
        foreach( $arrayTrad as $key => $value )
        {
            $value = trim( $value );
            $value = str_replace( '"', '\"', $value );
            if ( ! empty( $key ) ) $src.= "\t\t\"" . trim( $key ) . "\" => \"" . $value . "\",\n";
        }
        $src.= "\t];\n";
        $src.= "}\n";

        if ( ! empty( $lang ) )
        {
            Factory::getInstance()->File()->create( LANG_PATH . "/" . strtoupper( $lang ) . ".php" , $src );
        }

        echo json_encode([
            'result' => true,
            'msg'    => Translate::getInstance()->getText( 'msg_text_maj' ),
        ]);
    });

    $app->post('/traduction/add-key', function() use ($app) {
        $new_key = $app->request->post('new_key');

        $langs = \App\Kernel\Lang::getInstance()->getAll();
        foreach( $langs as $lang )
        {
            $filename = LANG_PATH . "/" . strtoupper( $lang->locale ) . ".php" ;

            if ( file_exists( $filename ) )
            {
                $className = "\Project\Lang\\" . strtoupper( $lang->locale ) ;
                $class     = new $className ;
                $arrayTrad = $class->getVar();
            }
            else
            {
                $arrayTrad = [];
            }

            $arrayTrad[$new_key] = "";

            ksort( $arrayTrad );

            $src = "<"."?php\n";
            $src.= "namespace Project\Lang;\n";
            $src.= "class " . strtoupper( $lang->locale ) . " extends \App\Kernel\Front\LanguageModel {\n";
            $src.= "\tprotected $"."a = [\n";
            foreach( $arrayTrad as $key => $value )
            {
                $value = trim( $value );
                $value = str_replace( '"', '\"', $value );
                if ( ! empty( $key ) ) $src.= "\t\t\"" . trim( $key ) . "\" => \"" . $value . "\",\n";
            }
            $src.= "\t];\n";
            $src.= "}\n";

            if ( ! empty( $lang ) ) Factory::getInstance()->File()->create( $filename , $src );
        }

        echo json_encode([
            'result' => true,
            'msg'    => Translate::getInstance()->getText( 'msg_key_add' ),
        ]);
    });

    $app->get('/traduction/remove-key/:key', function( $key ) use ($app) {
        $app->render('ext/langue/delete-key.twig.html', [
            'key' => $key
        ]);
    });

    $app->post('/traduction/remove-key-action', function() use ($app) {
        $key_name = $app->request->post('key');

        $langs = \App\Kernel\Lang::getInstance()->getAll();
        foreach( $langs as $lang )
        {
            $className = "\Project\Lang\\" . strtoupper( $lang->locale ) ;
            $class     = new $className ;
            $arrayTrad = $class->getVar();

            unset( $arrayTrad[$key_name] );

            ksort( $arrayTrad );

            $src = "<"."?php\n";
            $src.= "namespace Project\Lang;\n";
            $src.= "class " . strtoupper( $lang->locale ) . " extends \App\Kernel\Front\LanguageModel {\n";
            $src.= "\tprotected $"."a = [\n";
            foreach( $arrayTrad as $key => $value )
            {
                $value = trim( $value );
                $value = str_replace( '"', '\"', $value );
                if ( ! empty( $key ) ) $src.= "\t\t\"" . trim( $key ) . "\" => \"" . $value . "\",\n";
            }
            $src.= "\t];\n";
            $src.= "}\n";

            if ( ! empty( $lang ) ) Factory::getInstance()->File()->create( LANG_PATH . "/" . strtoupper( $lang->locale ) . ".php" , $src );
        }

        echo json_encode([
            'result' => true,
            'msg'    => Translate::getInstance()->getText( 'msg_key_suppr' ),
        ]);
    });
});