<?php

use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/redirect', function () use ($app)
{
    $app->get('/', function () use ($app) {

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

        $app->render('ext/redirect/index.twig', [ 'content' => $content ] );

    })->name('redirect301_index');

    $app->map('/edit', function () use ($app)
    {
    	$content = "";
		if ( $app->request->isPost() )
		{
			$db = \DB::get_db();
			$db->beginTransaction();
			$db->exec('TRUNCATE redirect_301');

			$content = $app->request->post('content');
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

        $app->render('ext/redirect/index.twig', [ 'content' => $content , 'result' => true ]);
    })->name('redirect301_edit')->via('POST');
});