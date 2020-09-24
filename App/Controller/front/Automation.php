<?php

use App\Kernel\Front\Data;

$app->get('/email/automation/recipient/:id', function ( $id ) use ( $app ) {
    $app->contentType('application/json');

    $Automation = new Data('EdAutomationHistory');
    $Automation->find( $id );

    echo $Automation->get('information');
})->name('email_automation_recipient');

$app->get('/email/automation/template/:id(/:recipientId)', function ( $id , $recipientId = NULL ) use ( $app ) {
    $Automation = new Data('EdAutomation');
    $rst = $Automation->find( $id );

    $html = $Automation->get('html');

    if ( $recipientId !== NULL )
    {
        $History = new Data('EdAutomationHistory');
        $rstH = $History->find( $recipientId );

        if ( $rstH )
        {
            if ( ! empty( $History->get('information') ) )
            {
                $json = json_decode( $History->get('information') , true );

                foreach( $json[ $History->get('email') ] as $key => $value )
                {
                    $html = str_replace( '[' . $key . ']' , $value , $html ) ;
                }

//                $tab = [] ;
//                $tab[ $History->get('email') ] = [ 'Email' => $History->get('email') ] ;
//
//                $History->set('information' , json_encode( $tab ) );
//                $History->save();
            }
        }
    }

    if ( $rst ) echo $html;
})->name('email_automation_template');
