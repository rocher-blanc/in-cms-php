<?php

$app->group('/domain', function () use ($app) {
    $app->get('/', function () use ($app) {
        $contentRows = \DB::for_table('domain')
            ->order_by_asc('domain_name')
            ->find_many();

        $app->render('admin/domain/index.twig.html', [
            "contentRows" => $contentRows
        ]);
    })->name('domain_index');

    $app->delete('/delete/:id', function ($id) use ($app) {
        $ret = false;
        $contentRow = \DB::for_table('domain')
            ->where_equal('domain_id', $id)
            ->find_one();

        if ($contentRow) {
            \App\Kernel\Back\Log::getInstance()->warning(53, $contentRow->domain_name);

            $msg = "Le nom de domaine a bien été supprimé";
            $ret = true;
            $contentRow->delete();
        } else {
            $msg = "Une erreur est survenue lors de la suppression";
        }

        echo json_encode(array("msg" => $msg, "result" => $ret));
    })->name('moduleadmin_delete');

    $app->map('/edit(/:id)', function ($id = -1) use ($app)
    {
        $error    = false ;
        $tabError = array() ;

        $contentRow = \DB::for_table('domain')
            ->where(array('domain_id' => $id))
            ->find_one();

        if ( $id != -1 && !$contentRow )
        {
            $app->redirect( $app->config('admin.url') . '/admin/domain');
        }
        else
        {
            $post = $contentRow ;
        }

        if ( $app->request->isPost() )
        {
            $post = array(
                "domain_name" => $app->request->post('domain_name')
            );

            if ($app->request->post('domain_name') == "")
            {
                $error = true;
                $tabError['domain_name'] = "Veuillez remplir ce champ";
            }
            else
            {
                $exist = \DB::for_table('domain')->where_equal('domain_name', $app->request->post('domain_name'));
                if ($id != -1) $exist = $exist->where_not_equal('domain_id', $id);
                $exist = $exist->count();
            }

            if ($app->request->post('domain_name') != "" && $exist > 0) {
                $error = true;
                $tabError['domain_name'] = "Ce nom de domaine est deja utilisé";
            }

            if ($error == false)
            {
                if (!$contentRow)
                {
                    $contentRow = DB::for_table('domain')->create();
                    $add = true;
                }

                $contentRow->domain_name = $app->request->post('domain_name');
                $contentRow->save();

                \App\Kernel\Back\Log::getInstance()->info(($add == true ? 51 : 52), $contentRow->domain_name);

                $id = $contentRow->domain_id;

                $app->flash('__msg', addslashes(json_encode("Le nom de domaine a bien été " . ($add == true ? "ajouté" : "modifié"))));
                $app->flash('__result', true);

                if ($app->request->post('submit') == "stay") $app->redirect($app->config('admin.url') . '/admin/domain/edit/' . $id);
                else                                         $app->redirect($app->config('admin.url') . '/admin/domain');
            }
        }

        $app->render('admin/domain/edit.twig.html', array(
            "contentRow" => $contentRow,
            "id" 		 => $id,
            "post"		 => $post,
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError )));

    })->name('domain_edit')->via('GET', 'POST');
});