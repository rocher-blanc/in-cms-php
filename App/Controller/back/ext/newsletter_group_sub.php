<?php

$app->group('/newsletter_group_sub', function () use ($app)
{
    $app->get('/delete/:id', function ($id) use ($app) {
        $app->render('common/delete.twig', [
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/newsletter_group_sub/delete/' . $id)
        ]);
    });

    $app->post('/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('newsletter_group_sub')
            ->where_equal('newsletter_group_sub_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            $subs = \DB::for_table('newsletter_sub')
                ->where_equal('newsletter_sub_newsletter_group_sub_id' , $id)
                ->delete_many();

            \App\Kernel\Back\Log::getInstance()->warning( 202 , $contentRow->newsletter_group_sub_name );

            $msg = "Le groupe d'abonnés a bien été supprimé";
            $ret = true;
            $contentRow->delete();
        }
        else
        {
            $msg = "Une erreur est survenue lors de la suppression" ;
        }

        $result['msg'] = $msg;
        $result['result'] = $ret;
        $result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/ext/newsletter_group_sub') ;

        \App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
    })->name('groupmodule_delete');

    $app->get('/', function () use ($app) {

        $tab = [];
        $contentRows = \DB::for_table('newsletter_group_sub')
            ->order_by_asc('newsletter_group_sub_name')
            ->find_many();

        if ( $contentRows )
        {
            foreach( $contentRows as $row )
            {
                $std = new \stdClass;
                $std->newsletter_group_sub_id = $row->newsletter_group_sub_id;
                $std->newsletter_group_sub_name = $row->newsletter_group_sub_name;
                $std->count = \DB::for_table('newsletter_sub')->where_equal('newsletter_sub_newsletter_group_sub_id',$row->newsletter_group_sub_id)->count();
                $tab[] = $std ;
            }
        }

        $app->render('ext/newsletter_group_sub/index.twig.html', [
            "contentRows" => $tab
        ]);

    })->name('newsletter_group_sub_index');

    $app->get('/subscriber/:id', function ($group_id) use ($app) {

        $contentRows = \DB::for_table('newsletter_sub')
            ->where_equal('newsletter_sub_newsletter_group_sub_id',$group_id)
            ->order_by_asc('newsletter_sub_email')
            ->find_many();

        $contentRow = \DB::for_table('newsletter_group_sub')
            ->where(['newsletter_group_sub_id' => $group_id])
            ->find_one();

        $app->render('ext/newsletter_group_sub/subscriber.twig.html', [
            "id" => $group_id,
            "name" => $contentRow->newsletter_group_sub_name,
            "contentRows" => $contentRows
        ]);

    })->name('newsletter_group_sub_subscriber');

    $app->get('/subscriber/:id/import', function ($group_id) use ($app) {

        $contentRow = \DB::for_table('newsletter_group_sub')
            ->where(['newsletter_group_sub_id' => $group_id])
            ->find_one();

        if ( $app->request->isPost() ) {
            if ( $app->request->post('emails') == "" ) {
                $error = true ;
                $tabError['emails'] = "Veuillez remplir ce champ" ;
            }

            if ( $error == false ) {
                $exp  = explode("\n" , $app->request->post('emails') );

                if ( $exp )
                {
                    foreach( $exp as $email )
                    {
                        $email = str_replace( "\r" , "" , $email );
                        if ( filter_var( $email , FILTER_VALIDATE_EMAIL ) )
                        {
                            $ct = \DB::for_table('newsletter_sub')
                                ->where_equal('newsletter_sub_email' , $email )
                                ->where_equal('newsletter_sub_newsletter_group_sub_id' , $group_id )
                                ->count();

                            if ( $ct == 0 )
                            {
                                $row = \DB::for_table('newsletter_sub')->create();
                                $row->newsletter_sub_email = $email ;
                                $row->newsletter_sub_newsletter_group_sub_id = $group_id ;
                                $row->save();
                            }
                        }
                    }
                }

                \App\Kernel\Back\Log::getInstance()->info( 203 , $contentRow->newsletter_group_sub_name ) ;

                $app->flash('__msg',addslashes( "Les emails ont bien été ajouté" ) );
                $app->flash('__result',true);

                if ( $app->request->post('submit') == "stay" ) 	$app->redirect( $app->config('admin.url') . '/ext/newsletter_group_sub/subscriber/' . $group_id . "/import" );
                else 											$app->redirect( $app->config('admin.url') . '/ext/newsletter_group_sub/subscriber/' . $group_id );
            }
        }

        $app->render('ext/newsletter_group_sub/subscriber_import.twig.html', [
            "id" => $group_id,
            "name" => $contentRow->newsletter_group_sub_name,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )
        ]);

    })->name('newsletter_group_sub_import')->via('GET', 'POST');

    $app->get('/subscriber/download/:id', function ($id) use ($app) {
        $contentRow = \DB::for_table('newsletter_sub')
            ->where_equal('newsletter_sub_newsletter_group_sub_id' , $id)
            ->find_many();

        if ( $contentRow )
        {
            header("Content-Type: text/plain");
            header("Content-disposition: attachment; filename=export_group_newsletter_".$id.".csv;");

            $array  = [];
            $output = '' ;
            $i      = 0;

            foreach( $contentRow as $row )
            {
                $array = [
                    'email' => $row->newsletter_sub_email
                ];

                if ( $i > 0 ) $output.= "\n" ;
                $output.= implode( ";" , $array ) ;
                $i++;
            }

            echo trim( $output ) ;
        }
        else
        {
            $app->redirect( $app->config('admin.url') . '/ext/newsletter_group_sub');
        }
    });

    $app->get('/subscriber/delete/:id', function ($id) use ($app) {
        $app->render('common/delete.twig', [
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/newsletter_group_sub/subscriber/delete/' . $id)
        ]);
    });

    $app->post('/subscriber/delete/:id', function ($id) use ($app)
    {
        $ret = false ;
        $contentRow = \DB::for_table('newsletter_sub')
            ->where_equal('newsletter_sub_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            $group_id = $contentRow->newsletter_sub_newsletter_group_sub_id ;
            \App\Kernel\Back\Log::getInstance()->warning( 204 , $contentRow->newsletter_sub_email ) ;

            $msg = "L'abonné a bien été supprimé" ;
            $ret = true ;
            $contentRow->delete();

            $url = $app->config('admin.url') . '/ext/newsletter_group_sub/subscriber/' . $group_id ;
        }
        else
        {
            $msg = "Une erreur est survenue lors de la suppression" ;

            $url = $app->config('admin.url') . '/ext/newsletter_group_sub' ;
        }

        echo json_encode([
            "msg" => $msg,
            "result" => $ret,
            "url" => $url
        ]) ;
    })->name('newsletter_subscriber_delete');

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
                $error    = true ;
                $errorMsg = "Veuillez indiquer le nom" ;
            }
            else {
                $exist = \DB::for_table('newsletter_group_sub')->where_equal('newsletter_group_sub_name' , $app->request->post('newsletter_group_sub_name'));
                if ( $id != -1 ) $exist = $exist->where_not_equal('newsletter_group_sub_id' , $id);
                $exist = $exist->count();
            }

            if ( $app->request->post('newsletter_group_sub_name') != "" && $exist > 0 && $error != true ) {
                $error    = true ;
                $errorMsg = "Ce nom de groupe est deja utilisé" ;
            }

            if ( $error == false ) {
                if ( !$contentRow ) {
                    $contentRow = DB::for_table('newsletter_group_sub')->create();
                    $add = true ;
                }

                $contentRow->newsletter_group_sub_name = $app->request->post('newsletter_group_sub_name');
                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 200 : 201 ) , $contentRow->newsletter_group_sub_name ) ;

                $errorMsg = "Le groupe d'abonnés a bien été " . ( $add == true ? "ajouté" : "modifié" ) ;
            }

            $result['msg'] = $errorMsg;
            $result['result'] = ( ! $error );

            if ( $error )
            {
                \App\Kernel\Factory::getInstance()->Response()->flash( $result['msg'] ) ;
            }
            else
            {
                \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect($result['msg'] , $result['result'] , '/ext/newsletter_group_sub' ) ;
            }
        }

        $app->render('ext/newsletter_group_sub/edit.twig.html', array(
            "contentRow" => $contentRow,
            "id" 		 => $id,
            "post"		 => $post,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )));

    })->name('newsletter_group_sub_edit')->via('GET', 'POST');
});