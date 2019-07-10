<?php

use App\Kernel\Factory;

$app->group('/translate', function () use ($app) {

    $app->get('/', function () use ($app) {

		$contentRows = \App\Kernel\Lang::getInstance()->getBack();

		$app->render('admin/translate/index.twig', [
			"contentRows" => $contentRows
		]);

    })->name('admin_translate_index');


	$app->post('/get-lang', function() use ($app) {
		$lang_abbr = $this->getApp()->request->post('lang_locale');

        $keys = [];
        $lang = strtoupper( $lang_abbr ) ;
        if ( file_exists( LANG_PATH . '/BO' . $lang . '.php' ) )
        {
            $className = "\Project\Lang\\BO" . strtoupper( $lang_abbr ) ;
            $class     = new $className ;
            $arrayTrad = $class->getVar();

            ksort( $arrayTrad );

            foreach( $arrayTrad as $key => $value )
            {
                if( !empty($key) )
                {
                    $keys[$key] = [
                        'type'  => "text", //html
                        'value' => $value
                    ];
                }
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


	$app->post('/update-translate', function() use ($app) {
		$key   = $app->request->post('key');
		$lang  = $this->getApp()->request->post('lang');
		$type  = $app->request->post('type');
		$value = $app->request->post('value');

        $arrayTrad = [];
        $filename  = LANG_PATH . "/BO" . strtoupper( $lang ) . ".php" ;

        if ( file_exists( $filename ) )
        {
            $className = "\Project\Lang\\BO" . strtoupper( $lang ) ;
            $class     = new $className ;
            $arrayTrad = $class->getVar();

            ksort( $arrayTrad );
        }

        $arrayTrad[$key] = $value;

		$src = "<"."?php\n";
		$src.= "namespace Project\Lang;\n";
		$src.= "class BO" . strtoupper( $lang ) . " extends \App\Kernel\Front\LanguageModel {\n";
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
			Factory::getInstance()->File()->create( LANG_PATH . "/BO" . strtoupper( $lang ) . ".php" , $src );
		}

		echo json_encode([
							 'result' => true,
							 'msg'    => "Le texte a été mis à jour."
						 ]);
	});


	$app->post('/add-key', function() use ($app) {
		$new_key = $app->request->post('new_key');

		$langs = \App\Kernel\Lang::getInstance()->getBack();
		foreach( $langs as $lang )
		{
			$filename = LANG_PATH . "/BO" . strtoupper( $lang->locale ) . ".php" ;

			if ( file_exists( $filename ) )
			{
				$className = "\Project\Lang\\BO" . strtoupper( $lang->locale ) ;
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
			$src.= "class BO" . strtoupper( $lang->locale ) . " extends \App\Kernel\Front\LanguageModel {\n";
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
             'msg'    => "La clef a bien été ajouté",
         ]);
	});


	$app->get('/remove-key/:key', function( $key ) use ($app) {
		$app->render('ext/langue/delete-key.twig.html', [
			'key' => $key
		]);
	});


	$app->post('/remove-key-action', function() use ($app) {
		$key_name = $app->request->post('key');

		$langs = \App\Kernel\Lang::getInstance()->getBack();
		foreach( $langs as $lang )
		{
            $filename = LANG_PATH . "/BO" . strtoupper( $lang->locale ) . ".php" ;

            if ( file_exists( $filename ) )
            {
                $className = "\Project\Lang\\BO" . strtoupper( $lang->locale ) ;
                $class     = new $className ;
                $arrayTrad = $class->getVar();

                unset( $arrayTrad[$key_name] );

                ksort( $arrayTrad );

                $src = "<"."?php\n";
                $src.= "namespace Project\Lang;\n";
                $src.= "class BO" . strtoupper( $lang->locale ) . " extends \App\Kernel\Front\LanguageModel {\n";
                $src.= "\tprotected $"."a = [\n";
                foreach( $arrayTrad as $key => $value )
                {
                    $value = trim( $value );
                    $value = str_replace( '"', '\"', $value );
                    if ( ! empty( $key ) ) $src.= "\t\t\"" . trim( $key ) . "\" => \"" . $value . "\",\n";
                }
                $src.= "\t];\n";
                $src.= "}\n";

                if ( ! empty( $lang ) ) Factory::getInstance()->File()->create( LANG_PATH . "/BO" . strtoupper( $lang->locale ) . ".php" , $src );
            }

		}

		echo json_encode([
							 'result' => true,
							 'msg'    => "La clé a été supprimée",
						 ]);
	});

});