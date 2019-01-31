<?php

use App\Kernel\Front\Data;

$app->get('/email/automation/recipient/:id', function ( $id ) use ( $app ) {
    $app->contentType('application/json');

    $Automation = new Data('EdAutomationHistory');
    $Automation->find( $id );

    echo $Automation->get('information');
})->name('email_automation_recipient');

$app->get('/email/automation/template/:id', function ( $id ) use ( $app ) {
    $Automation = new Data('EdAutomation');
    $rst = $Automation->find( $id );

    if ( $rst ) echo $Automation->get('html');
})->name('email_automation_template');
