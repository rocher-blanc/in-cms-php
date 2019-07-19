<?php

use App\Kernel\Front\Translate;

$app->group('/secured', function () use ($app) {
    // CONNEXION
    $app->map('/login', function () use ($app)
    {
        $app->render('secured/login.twig.html') ;
    })->via('GET', 'POST')->name('secured_login');

    // PROFILE
    $app->post('/upload', function () use ($app) {
        $name = basename($_FILES[ $app->request->post('field') ]["name"]);
        $ext = explode( '.' , $name );
        $extension = end( $ext );
        $name = basename( $name , '.' . $extension );
        $name = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;

        $rst = move_uploaded_file( $_FILES[ $app->request->post('field') ]["tmp_name"] , IMAGE_PATH . "/_avatar/" . $name );

        $std = new \stdClass;
        $std->name = str_replace( WEB_PATH , '' , IMAGE_PATH . "/_avatar/" . $name );
        $std->url = \App\Kernel\Factory::getInstance()->Url()->image( "_avatar/" . $name , true ) ;
        $std->rst = $rst ;

        \App\Kernel\Factory::getInstance()->Response()->printJSON( $std );
    });

    $app->get('/profile', function () use ($app) {
        $error    = false ;
        $tabError = array() ;
        $id 	  = $_SESSION[ $app->config('session') ]['id'] ;

        $user = \DB::for_table('user')
            ->where_equal('user_id' , $id)
            ->find_one();

        if ( !$user ) {
            $app->redirect( $app->config('admin.url') . '/');
        }

        if ( $app->request->isPost() ) {
            if ( $app->request->post('user_name') == "" ) {
                $error = true ;
                $tabError['user_name'] = Translate::getInstance()->getText( 'mandatory_fillin' );
            }
            else {
                $exist = \DB::for_table('user')
                    ->where_equal('user_name' , $app->request->post('user_name'))
                    ->where_not_equal('user_id' , $id)
                    ->count();
            }

            if ( $app->request->post('user_name') != "" && $exist > 0 ) {
                $error = true ;
                $tabError['user_name'] = Translate::getInstance()->getText( 'already_use_login' );
            }

            if ( $app->request->post('user_fname') == "" ) {
                $error = true ;
                $tabError['user_fname'] = Translate::getInstance()->getText( 'mandatory_fillin' );
            }

            if ( $app->request->post('user_lname') == "" ) {
                $error = true ;
                $tabError['user_lname'] = Translate::getInstance()->getText( 'mandatory_fillin' );
            }

            if ( $app->request->post('last_password') != "" && password_verify( $app->request->post('last_password'), $user->user_password) === false ) {
                $error = true ;
                $tabError['last_password'] = Translate::getInstance()->getText( 'err_password_old' );
            }

            if ( $app->request->post('last_password') == "" && $app->request->post('password') != "" && $app->request->post('confirm_password') != '' ) {
                $error = true ;
                $tabError['last_password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
            }

            if ( $app->request->post('last_password') != "" && $app->request->post('password') == "" && $app->request->post('confirm_password') != '' ) {
                $error = true ;
                $tabError['password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
            }

            if ( $app->request->post('last_password') != "" && $app->request->post('confirm_password') == "" && $app->request->post('password') == '' ) {
                $error = true ;
                $tabError['password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
                $tabError['confirm_password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
            }

            if ( $app->request->post('last_password') != "" && $app->request->post('confirm_password') == "" && $app->request->post('password') != '' ) {
                $error = true ;
                $tabError['confirm_password'] = Translate::getInstance()->getText( 'mandatory_fillin' );
            }

            if ( $app->request->post('last_password') != "" && $app->request->post('password') != $app->request->post('confirm_password') ) {
                $error = true ;
                $tabError['confirm_password'] = Translate::getInstance()->getText( 'msg_different_password' );
            }

            if ( $error == false ) {
                if ( $app->request->post('password') != "" ) {
                    $user->user_password = password_hash( $app->request->post('password') ,PASSWORD_BCRYPT,['cost' => 9]) ;
                }

                $user->user_name  = $app->request->post('user_name');
                $user->user_fname = $app->request->post('user_fname');
                $user->user_lname = $app->request->post('user_lname');
                $user->save();

                \App\Kernel\Factory::getInstance()->Response()->flashAndRedirect( "Votre profil est modifié" , true , '/secured/profile' );
            }
        }

        $app->render('secured/profile.twig.html', array(
            "error"		 => ( $error === false ? "0" : "1" ),
            "tabError"	 => json_encode( $tabError ))) ;
    })->via('GET', 'POST')->name('secured_profile');

    // DECONNEXION
    $app->get('/logout', function () use ($app) {

    })->name('secured_logout');

    // ACCES INTERDIT
    $app->get('/forbidden', function () use ($app) {
        $app->render('errors/403.twig');
    })->name('secured_forbidden');
});