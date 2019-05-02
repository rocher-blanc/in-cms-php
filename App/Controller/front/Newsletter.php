<?php

use App\Api\Easyletter;
use App\Kernel\Front\Data;
use App\Kernel\Container;
use App\Kernel\Http;

$app->get('/email/newsletter/recipient/:id', function ( $id ) use ( $app ) {
    $app->contentType('application/json');

    $Newsletter = new Data('NewsletterCampaign');
    $Newsletter->find( $id );

    if ( $Newsletter->get('type') == 1 )
    {
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
                    $mail = trim( $email->get( $r->getEntity()->get('email')->getColumn() ) ) ;
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
    }
    else if ( $Newsletter->get('type') == 2 )
    {
        $Parent = new Data('NewsletterCampaign');
        $Parent->find( $Newsletter->get('newsletter_parent') );

        $el = new Easyletter;
        $tabStats = $el->stats( $Parent->get('id_easyletter') );

        if ( $tabStats )
        {
            $records = $tabStats['destStats']['records'];
            for( $i = 0; $i < count($records); $i++ )
            {
                if ( $records[$i][4] == 0 )
                {
                    $mail = $records[$i][1] ;
                    $tab[ $mail ] = [
                        'Email' => $mail,
                        'lien_desinscription' => Http::getInstance()->getUrl() . "/newsletter/unsubscribe/" . $Newsletter->get('element_module_parent_id') . "/" . $mail
                    ] ;
                }
            }
        }
    }
    else if ( $Newsletter->get('type') == 3 )
    {
        $Parent = new Data('NewsletterCampaign');
        $Parent->find( $Newsletter->get('newsletter_parent') );

        $el = new Easyletter;
        $tabStats = $el->stats( $Parent->get('id_easyletter') );

        if ( $tabStats )
        {
            $records = $tabStats['destStats']['records'];
            for( $i = 0; $i < count($records); $i++ )
            {
                if ( $records[$i][9] == 0 )
                {
                    $mail = $records[$i][1] ;
                    $tab[ $mail ] = [
                        'Email' => $mail,
                        'lien_desinscription' => Http::getInstance()->getUrl() . "/newsletter/unsubscribe/" . $Newsletter->get('element_module_parent_id') . "/" . $mail
                    ] ;
                }
            }
        }
    }

    echo json_encode( $tab );
})->name('newsletter_recipient');

$app->get('/email/newsletter/template/:id', function ( $id ) use ( $app ) {
    $model = new Data('NewsletterModel');
    $rstModel = $model->find( $id ) ;

    if ( $rstModel )
    {
        echo $model->get('html');
    }
})->name('newsletter_template');

$app->get('/newsletter/unsubscribe/:id/:email(/:confirm)', function ( $id , $email , $confirm = 0 ) use ( $app ) {
    $newsletter = new Data('NewsletterCampaignGroup');
    $rstNewsletter = $newsletter->find( $id ) ;

    $module = new \App\Kernel\Common\Module;
    $idModule = $module->getId('NewsletterCampaignGroup') ;

    $unsubCt = new Data('NewsletterCampaignGroupUnsubscribe');
    $rst = $unsubCt->count([
        'email' => $email,
        'module_id' => $idModule,
        'element_id' => $id
    ]);

    $unsub = false ;
    if ( $confirm == 1 && $rst == 0 )
    {
        $unsub = new Data('NewsletterCampaignGroupUnsubscribe');
        $unsub->findOrCreate([
            'email' => $email,
            'module_id' => $idModule,
            'element_id' => $id
        ]);
        $unsub->save();
        
        $unsub = true ;
        $rst   = 1 ;
    }

    $app->render('Newsletter/unsubscribe.twig' , [
        'newsletter_name' => $newsletter->get('name'),
        'idnl' => $id,
        'email' => $email,
        'unsub' => $unsub,
        'unsubscribe' => ( $rst == 0 ? false : true )
    ]);

})->name('newsletter_unsubscribe');
