<?php

$app->group('/newsletter_model', function () use ($app)
{
    $app->get('/', function () use ($app) {

        $contentRows = \DB::for_table('newsletter_model')
            ->order_by_asc('newsletter_model_name')
            ->find_many();

        $app->render('ext/newsletter_model/index.twig.html', [
            "contentRows" => $contentRows
        ]);

    })->name('newsletter_model_index');

    $app->map('/edit(/:id)', function ($id = -1) use ($app)
    {
        $error    = false ;
        $tabError = [] ;

        $contentRow = \DB::for_table('newsletter_model')
            ->where(['newsletter_model_id' => $id])
            ->find_one();

        if ( $id != -1 && !$contentRow ) {
            $app->redirect( $app->config('admin.url') . '/ext/newsletter_model');
        }
        else {
            $post = $contentRow ;
        }

        if ( $app->request->isPost() ) {
            $post = [
                "newsletter_model_name" => $app->request->post('newsletter_model_name')
            ];

            if ( $app->request->post('newsletter_model_name') == "" ) {
                $error = true ;
                $msgError = "Veuillez remplir ce champ" ;
            }

            if ( $error == false ) {
                if ( !$contentRow ) {
                    $contentRow = DB::for_table('newsletter_model')->create();
                    $add = true ;
                }

                $contentRow->newsletter_model_name = $app->request->post('newsletter_model_name');
                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 205 : 206 ) , $contentRow->newsletter_model_name ) ;

                $id = $contentRow->newsletter_model_id ;

                \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect( "Le gabarit a bien été " . ( $add == true ? "ajouté" : "modifié" ) , true , '/ext/newsletter_model' );
            }
            else
            {
                \App\Kernel\Factory::getInstance()->Response()->flash( $msgError );
            }
        }

        $app->render('ext/newsletter_model/edit.twig.html', [
            "contentRow" => $contentRow,
            "id" 		 => $id,
            "post"		 => $post,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )
        ]);

    })->name('newsletter_model_edit')->via('GET', 'POST');

    $app->get('/delete/:id', function ($id) use ($app) {
        $app->render('common/delete.twig', [
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/newsletter_model/delete/' . $id)
        ]);
    });

    $app->get('/draw/:id', function ($id) use ($app) {
        $app->render('ext/newsletter_model/draw.twig', [
            "id"     => $id,
            "apiKey" => "DCSCyS2S1STYFy0EpQJByYkLSIl8eCetqNJ6Awh79Ykk4Qh0WymMJsTqZYLW",
            "userId" => "easydoor"
        ]);
    });

    $app->post('/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('newsletter_model')
            ->where_equal('newsletter_model_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 207 , $contentRow->newsletter_model_name ) ;

            $msg = "Le gabarit a bien été supprimé" ;
            $ret = true ;
            $contentRow->delete();
        }
        else
        {
            $msg = "Une erreur est survenue lors de la suppression" ;
        }

        echo json_encode([
            "msg" => $msg,
            "result" => $ret,
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/newsletter_model')
        ]) ;
    })->name('newsletter_model_delete');

    $app->post('/save/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('newsletter_model')
            ->where_equal('newsletter_model_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 208 , $contentRow->newsletter_model_name ) ;

            $msg = "Le gabarit a bien été sauvegardé" ;
            $ret = true ;
            $contentRow->newsletter_model_html = $app->request->post('html');
            $contentRow->save();
        }
        else
        {
            $msg = "Une erreur est survenue lors de la sauvegarde" ;
        }

        echo json_encode([
            "msg" => $msg,
            "result" => $ret,
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/newsletter_model')
        ]) ;
    })->name('newsletter_model_save');
});