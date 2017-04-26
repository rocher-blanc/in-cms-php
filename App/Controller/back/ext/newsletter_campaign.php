<?php

$app->group('/newsletter_campaign', function () use ($app)
{
    $app->get('/', function () use ($app) {

       $app->render('ext/newsletter_campaign/index.twig.html', [

       ]);

    })->name('newsletter_campaign_index');

    $app->map('/edit(/:id)', function ($id = -1) use ($app)
    {
        $error    = false ;
        $tabError = [] ;

        $contentRow = \DB::for_table('newsletter_sender')
            ->where(['newsletter_sender_id' => $id])
            ->find_one();

        if ( $id != -1 && !$contentRow ) {
            $app->redirect( $app->config('admin.url') . '/ext/newsletter_sender');
        }
        else {
            $post = $contentRow ;
        }

        if ( $app->request->isPost() ) {
            $post = [
                "newsletter_sender_name" => $app->request->post('newsletter_sender_name'),
                "newsletter_sender_email" => $app->request->post('newsletter_sender_email')
            ];

            if ( $app->request->post('newsletter_sender_name') == "" ) {
                $error = true ;
                $tabError['newsletter_sender_name'] = "Veuillez remplir ce champ" ;
            }

            if ( $app->request->post('newsletter_sender_email') == "" ) {
                $error = true ;
                $tabError['newsletter_sender_email'] = "Veuillez remplir ce champ" ;
            }
            else {
                if ( ! filter_var( $app->request->post('newsletter_sender_email') , FILTER_VALIDATE_EMAIL ) ) {
                    $error = true ;
                    $tabError['newsletter_sender_email'] = htmlentities("L'adresse email est invalide",ENT_QUOTES) ;
                }
            }

            if ( $error == false ) {
                if ( !$contentRow ) {
                    $contentRow = DB::for_table('newsletter_sender')->create();
                    $add = true ;
                }

                $contentRow->newsletter_sender_name = $app->request->post('newsletter_sender_name');
                $contentRow->newsletter_sender_email = $app->request->post('newsletter_sender_email');
                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 203 : 204 ) , $contentRow->newsletter_sender_name ) ;

                $id = $contentRow->newsletter_sender_id ;

                $app->flash('__msg',addslashes( json_encode( "L'expéditeur a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
                $app->flash('__result',true);

                if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/newsletter_sender/edit/' . $id );
                else 											$app->redirect( $app->config('admin.url') . '/ext/newsletter_sender' );
            }
        }

        $app->render('ext/newsletter_sender/edit.twig.html', [
            "contentRow" => $contentRow,
            "id" 		 => $id,
            "post"		 => $post,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )
        ]);

    })->name('newsletter_sender_edit')->via('GET', 'POST');

    $app->delete('/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('newsletter_sender')
            ->where_equal('newsletter_sender_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 205 , $contentRow->newsletter_sender_name ) ;

            $msg = "L'expéditeur a bien été supprimé" ;
            $ret = true ;
            $contentRow->delete();
        }
        else
        {
            $msg = "Une erreur est survenue lors de la suppression" ;
        }

        echo json_encode([
            "msg" => $msg,
            "result" => $ret
        ]) ;
    })->name('newsletter_sender_delete');
});