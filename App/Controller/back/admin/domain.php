<?php

use App\Kernel\Front\Translate;

$app->group('/domain', function () use ($app) {
    $app->get('/', function () use ($app) {
        $contentRows = \DB::for_table('domain')
            ->order_by_asc('domain_name')
            ->find_many();

        $app->render('admin/domain/index.twig.html', [
            "contentRows" => $contentRows
        ]);
    })->name('domain_index');

    $app->post('/delete/:id', function ($id) use ($app)
    {
        $result['result'] = false;
        $contentRow = \DB::for_table('domain')
            ->where_equal('domain_id', $id)
            ->find_one();

        if ($contentRow)
        {
            \App\Kernel\Back\Log::getInstance()->warning(53, $contentRow->domain_name);

            $result['msg'] = Translate::getInstance()->getText( domain_supr_msg);
            $result['result'] = true;
            $result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/admin/domain') ;

            $contentRow->delete();
        }
        else
        {
            $result['msg'] = Translate::getInstance()->getText( 'domain_supr_error');
        }

        \App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
    });

    $app->get('/delete/:id', function ($id) use ($app) {
        $app->render('common/delete.twig', [
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/admin/domain/delete/' . $id)
        ]);
    });

    $app->post('/edit(/:id)', function ($id = -1) use ($app)
    {
        $error      = false ;
        $result = [
            'result' => false
        ];
        $contentRow = NULL ;

        if ( $id != -1 )
        {
            $contentRow = \DB::for_table('domain')
                ->where(array('domain_id' => $id))
                ->find_one();
        }

        if ( $app->request->post('domain_name') == "" )
        {
            $error = true;
            $result['msg'] = Translate::getInstance()->getText( 'mandatory_domain_name');
            $result['field'] = 'domain_name' ;
        }
        else
        {
            $exist = \DB::for_table('domain')->where_equal('domain_name', $app->request->post('domain_name'));
            if ($id != -1) $exist = $exist->where_not_equal('domain_id', $id);
            $exist = $exist->count();
        }

        if ( ! $error && $exist > 0) {
            $error = true;
            $result['msg'] = Translate::getInstance()->getText( 'mandatory_domain_name');
            $result['field'] = 'domain_name' ;
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

            $result['msg'] = Translate::getInstance()->getText( 'domain_name_part') . ($add == true ? Translate::getInstance()->getText( 'added') : Translate::getInstance()->getText( 'modified') ) ;
            $result['result'] = true ;
            $result['url'] = \App\Kernel\Factory::getInstance()->Url()->get('/admin/domain') ;
        }

        \App\Kernel\Factory::getInstance()->Response()->printJSON($result) ;
    });

    $app->get('/edit(/:id)', function ($id = -1) use ($app)
    {
        $contentRow = NULL ;
        if ( $id != -1 )
        {
            $contentRow = \DB::for_table('domain')
                ->where(array('domain_id' => $id))
                ->find_one();
        }

        if ( $id != -1 && !$contentRow )
        {
            $app->redirect( $app->config('admin.url') . '/admin/domain');
        }
        else
        {
            $post = $contentRow ;
        }

        $app->render('admin/domain/edit.twig.html', array(
            "id" 		 => $id,
            "post"		 => $contentRow
        ));
    });
});