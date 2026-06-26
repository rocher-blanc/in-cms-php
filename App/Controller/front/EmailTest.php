<?php

use App\Kernel\Front\Data;

$app->get('/email/test/recipient/:email', function ( $email ) use ( $app ) {
    $app->contentType('application/json');

    $tab = [] ;
    $tab[ $email ] = [ 'Email' => $email ] ;

    echo json_encode( $tab );
})->setName('email_test_recipient');

$app->get('/email/test/template/:module/:id', function ( $module , $id ) use ( $app ) {
    $Model = new Data( $module );
    $rst = $Model->find( $id );

    if ( $rst ) echo $Model->get('html');
})->setName('email_test_template');