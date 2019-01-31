<?php

use App\Kernel\Front\Data;

$app->get('/email/test/recipient/:id', function ( $id ) use ( $app ) {
    $app->contentType('application/json');

    $Automation = new Data('EdAutomationHistory');
    $Automation->find( $id );

    echo $Automation->get('information');
})->name('email_test_recipient');

$app->get('/email/test/template/:id', function ( $id ) use ( $app ) {
    $Automation = new Data('EdAutomation');
    $rst = $Automation->find( $id );

    if ( $rst ) echo $Automation->get('html');
})->name('email_test_template');
