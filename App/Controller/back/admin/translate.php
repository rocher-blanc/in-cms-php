<?php

use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/translate', function (\Slim\Routing\RouteCollectorProxy $app) {

    $app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {

		$contentRows = \App\Kernel\Lang::getInstance()->getBack();

		return \App\Kernel\AppContext::twig()->render($res, 'admin/translate/index.twig', [
			"contentRows" => $contentRows
		]);

    })->name('admin_translate_index');


	$app->post('/get-lang', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
        header('Content-Type: application/json;charset=utf-8');
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

		$result = json_encode([
            'result' => true,
            'msg'    => "",
            'lang'   => $lang,
            'keys'   => $keys,
        ], JSON_UNESCAPED_UNICODE);

        echo $result;
	});


	$app->post('/update-translate', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
		$key   = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['key'] ?? '') : '');
		$lang  = $this->getApp()->request->post('lang');
		$type  = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['type'] ?? '') : '');
		$value = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['value'] ?? '') : '');

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
             'msg'    => Translate::getInstance()->getText('msg_text_maj')
         ]);
	});

	$app->post('/add-key', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
		$new_key = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['new_key'] ?? '') : '');

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
             'msg'    => Translate::getInstance()->getText('msg_key_add'),
         ]);
	});


	$app->get('/remove-key/:key', function( $key ) use ($app) {
		return \App\Kernel\AppContext::twig()->render($res, 'ext/langue/delete-key.twig.html', [
			'key' => $key
		]);
	});


	$app->post('/remove-key-action', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
		$key_name = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['key'] ?? '') : '');

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
							 'msg'    => Translate::getInstance()->getText('msg_key_suppr'),
						 ]);
	});

});