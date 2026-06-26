<?php

use App\Kernel\Config;
use App\Kernel\AppContext;
use App\Kernel\Factory;
use App\Kernel\Front\Translate;

$app->group('/user_front_group', function (\Slim\Routing\RouteCollectorProxy $app)
{
	$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {

		$contentRows = \DB::for_table('user_front_group')
                            ->order_by_asc('user_front_group_name')
                            ->find_many();
		
		return \App\Kernel\AppContext::twig()->render($res, 'ext/user_front_group/index.twig', array( "contentRows" => $contentRows ));

	})->setName('user_front_index');

	$app->map(['GET', 'POST'], '/edit[/{id}]', function ($id = -1) use ($app)
	{
		if ( $id == 1 )
		{
			$Guard = new \App\Kernel\Back\Acl;
			if ( $Guard->isAdmin() == false ) Factory::getInstance()->Response()->redirect( Config::getInstance()->get('forbidden.url') ) ;
		}
		
		$error    = false ;
		$tabError = array() ;
		
		$contentRow = \DB::for_table('user_front_group')
			->where(array('user_front_group_id' => $id))
			->find_one();

		if ( $id != -1 && !$contentRow ) {
			Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/user_front_group');
		}
		else {
			$post = $contentRow ;
		}
		
		if ( strtoupper($req->getMethod()) === 'POST' ) {
			$post = array(
				"user_front_group_name" => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_group_name'] ?? '') : '')
			) ;
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_group_name'] ?? '') : '') == "" ) {
				$error = true ;
				$tabError['user_front_group_name'] = Translate::getInstance()->getText( 'mandatory_fillin' );
			}
			else {
				$exist = \DB::for_table('user_front_group')->where_equal('user_front_group_name' , (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_group_name'] ?? '') : ''));
				if ( $id != -1 ) $exist = $exist->where_not_equal('user_front_group_id' , $id);
				$exist = $exist->count();
			}
			
			if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_group_name'] ?? '') : '') != "" && $exist > 0 ) {
				$error = true ;
				$tabError['user_front_group_name'] = Translate::getInstance()->getText( 'msg_grp_name_error' );
			}
			
			if ( $error == false ) {
				if ( !$contentRow ) {
					$contentRow = DB::for_table('user_front_group')->create();
					$add = true ;
				}
				
				$contentRow->user_front_group_name = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['user_front_group_name'] ?? '') : '');
				$contentRow->save();
				
				\App\Kernel\Back\Log::getInstance()->info( ( $add == true ? 7 : 8 ) , $contentRow->user_front_group_name ) ;
				
				$id = $contentRow->user_front_group_id ;
				
				AppContext::flash()->addMessage('__msg', "Le groupe a bien été " . ( $add == true ? "ajouté" : "modifié" ) );
				AppContext::flash()->addMessage('__result',true);
				
				if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['submit'] ?? '') : '') == "stay" ) 	Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/user_front_group/edit/' . $id );
				else 											Factory::getInstance()->Response()->redirect( Config::getInstance()->get('admin.url') . '/ext/user_front_group' );
			}
		}

		return \App\Kernel\AppContext::twig()->render($res, 'ext/user_front_group/edit.twig', array(
												"contentRow" => $contentRow,
												"id" 		 => $id,
												"post"		 => $post,
												"error"		 => ( $error === false ? "0" : "1" ),
												"tabError"	 => json_encode( $tabError )));

	})->setName('user_front_group_edit');

    $app->get('/delete/:id', function ($id) use ($app) {
        return \App\Kernel\AppContext::twig()->render($res, 'common/delete.twig', [
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/user_front_group/delete/' . $id)
        ]);
    });

	$app->post('/delete/:id', function ($id) use ($app)
	{
		$ret = false ;
        $contentRow = \DB::for_table('user_front_group')
            ->where_equal('user_front_group_id' , $id)
            ->find_one();

        if ( $contentRow )
        {
            \App\Kernel\Back\Log::getInstance()->warning( 6 , $contentRow->user_front_group_name ) ;

            $msg = Translate::getInstance()->getText('delete_groupe_success' );
            $ret = true ;
            $contentRow->delete();
        }
        else
        {
            $msg = Translate::getInstance()->getText('delete_error' );
        }

        AppContext::flash()->addMessage('__msg', $msg );
        AppContext::flash()->addMessage('__result',$ret);
		
		echo json_encode([
		    "msg" => $msg ,
            "url" => \App\Kernel\Factory::getInstance()->Url()->get('/ext/user_front_group'),
            "result" => $ret
        ]) ;
	})->setName('user_front_group_delete');
});