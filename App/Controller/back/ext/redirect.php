<?php

use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/redirect', function (\Slim\Routing\RouteCollectorProxy $app)
{
    $app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {

    	$content = [];
    	$req = \DB::for_table("redirect_301")
			->find_many();

    	if( $req )
		{
			foreach( $content as $row )
			{
				$content[] = $row->redirect_301_oldurl . ";" . $row->redirect_301_newurl ;
			}
		}

		$content = implode( "\n" , $content );

        return \App\Kernel\AppContext::twig()->render($res, 'ext/redirect/index.twig', [ 'content' => $content ]);

    })->name('redirect301_index');

    $app->map(['GET', 'POST'], '/edit', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
    {
    	$content = "";
		if ( strtoupper($req->getMethod()) === 'POST' )
		{
			$db = \DB::get_db();
			$db->beginTransaction();
			$db->exec('TRUNCATE redirect_301');

			$content = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['content'] ?? '') : '');
			$list = explode( "\n" , $content );

			foreach( $list as $row ) {
				$line = explode( ";" , $row );
				$old = trim( $line[0] );
				$new = trim( $line[1] );

				$prep = \DB::for_table("redirect_301")->create();
				$prep->redirect_301_oldurl = $old;
				$prep->redirect_301_newurl = $new;
				$prep->save();
			}
		}

        return \App\Kernel\AppContext::twig()->render($res, 'ext/redirect/index.twig', [ 'content' => $content , 'result' => true ]);
    })->name('redirect301_edit');
});