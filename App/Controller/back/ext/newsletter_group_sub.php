<?php

$app->group('/newsletter_group_sub', function () use ($app)
{
    $app->get('/', function () use ($app) {

        $contentRows = \DB::for_table('newsletter_group_sub')
            ->order_by_asc('newsletter_group_sub_name')
            ->find_many();

        $app->render('ext/newsletter_group_sub/index.twig.html', [
            "contentRows" => $contentRows
        ]);

    })->name('newsletter_group_sub_index');

    $app->map('/edit(/:id)', function ($id = -1) use ($app)
    {
        $error    = false ;
        $tabError = [] ;

        $contentRow = \DB::for_table('newsletter_group_sub')
            ->where(['newsletter_group_sub_id' => $id])
            ->find_one();

        if ( $id != -1 && !$contentRow ) {
            $app->redirect( $app->config('admin.url') . '/ext/newsletter_group_sub');
        }
        else {
            $post = $contentRow ;
        }

        if ( $app->request->isPost() ) {
            $post = [
                "newsletter_group_sub_name" => $app->request->post('newsletter_group_sub_name')
            ];

            if ( $app->request->post('newsletter_group_sub_name') == "" ) {
                $error = true ;
                $tabError['newsletter_group_sub_name'] = "Veuillez remplir ce champ" ;
            }
            else {
                $exist = \DB::for_table('newsletter_group_sub')->where_equal('newsletter_group_sub_name' , $app->request->post('newsletter_group_sub_name'));
                if ( $id != -1 ) $exist = $exist->where_not_equal('newsletter_group_sub_id' , $id);
                $exist = $exist->count();
            }

            if ( $app->request->post('newsletter_group_sub_name') != "" && $exist > 0 ) {
                $error = true ;
                $tabError['newsletter_group_sub_name'] = "Ce nom de groupe est deja utilisé" ;
            }

            if ( $error == false ) {
                if ( !$contentRow ) {
                    $contentRow = DB::for_table('newsletter_group_sub')->create();
                    $add = true ;
                }

                $contentRow->newsletter_group_sub_name = $app->request->post('newsletter_group_sub_name');
                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 200 : 201 ) , $contentRow->newsletter_group_sub_name ) ;

                $id = $contentRow->newsletter_group_sub_id ;

                $app->flash('__msg',addslashes( json_encode( "Le groupe d'abonnés a bien été " . ( $add == true ? "ajouté" : "modifié" ) ) ) );
                $app->flash('__result',true);

                if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/newsletter_group_sub/edit/' . $id );
                else 											$app->redirect( $app->config('admin.url') . '/ext/newsletter_group_sub' );
            }
        }

        $app->render('ext/newsletter_group_sub/edit.twig.html', array(
            "contentRow" => $contentRow,
            "id" 		 => $id,
            "post"		 => $post,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )));

    })->name('newsletter_group_sub_edit')->via('GET', 'POST');

    $app->delete('/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('newsletter_group_sub')
            ->where_equal('newsletter_group_sub_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 202 , $contentRow->newsletter_group_sub_name ) ;

            $msg = "Le groupe d'abonnés a bien été supprimé" ;
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
    })->name('newsletter_group_sub_delete');
});