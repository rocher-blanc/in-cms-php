<?php

use App\Kernel\Front\Data;
use App\Kernel\Container;
use App\Kernel\Http;

$app->get('/email/newsletter/recipient/:id', function ( $id ) use ( $app ) {
    $app->contentType('application/json');

    $Newsletter = new Data('NewsletterCampaign');
    $Newsletter->find( $id );

    $tab = [];

    $r = Container::getInstance()->module('NewsletterSubscriber');

    foreach( $Newsletter->get('recipient') as $group )
    {
        // on va chercher tous les destinataires
        $rstRec = $r->getRepository()
            ->getKit()
            ->where_equal( $r->getEntity()->get('element_module_parent_id')->getColumn() , $group )
            ->find_many();

        if ( $rstRec )
        {
            foreach( $rstRec as $email )
            {
                $mail = $email->get( $r->getEntity()->get('email')->getColumn() ) ;
                $tab[ $mail ] = [
                    'Email' => $mail,
                    'lien_desinscription' => Http::getInstance()->getUrl() . "/newsletter/unsubscribe/" . $Newsletter->get('element_module_parent_id') . "/" . $mail
                ] ;
            }
        }
    }

    $u = Container::getInstance()->module('NewsletterCampaignGroupUnsubscribe');

    $rstUn = $u->getRepository()
        ->getKit()
        ->where_equal( $u->getEntity()->get('element_id')->getColumn() , $Newsletter->get('element_module_parent_id') )
        ->find_many();

    if ( $rstUn )
    {
        foreach( $rstUn as $email )
        {
            unset( $tab[ $email->get('mod_newslettercampaigngroupunsubscribe_email') ] ) ; ;
        }
    }

    echo json_encode( $tab );
})->name('newsletter_recipient');

$app->get('/email/newsletter/template/:id', function ( $id ) use ( $app ) {
    $Newsletter = new Data('NewsletterCampaign');
    $rst = $Newsletter->find( $id );

    if ( $rst )
    {
        $model = new Data('NewsletterModel');
        $rstModel = $model->find( $Newsletter->get('template') ) ;

        if ( $rstModel )
        {
            echo $model->get('html');
        }
    }
})->name('newsletter_template');

$app->get('/newsletter/unsubscribe/:id/:email', function ( $id , $email ) use ( $app ) {
    $unsub = new Data('NewsletterCampaignGroupUnsubscribe');
    $unsub->create([
        'email' => $email,
        'element_module_parent_id' => $id
    ]);
    $unsub->save();
})->name('newsletter_unsubscribe');
