<?php

use App\Kernel\Front\Data;

$app->get('/email/automation/recipient/:email', function ( $email ) use ( $app ) {
    $app->contentType('application/json');

    $tab = [] ;
    $tab[ $email ] = [ 'Email' => $email ] ;

    echo json_encode( $tab );
})->name('email_automation_recipient');

$app->get('/email/automation/template/:module/:id', function ( $module , $id ) use ( $app ) {
    $Model = new Data( $module );
    $rst = $Model->find( $id );

    if ( $rst ) echo $Model->get('html');
})->name('email_automation_template');
