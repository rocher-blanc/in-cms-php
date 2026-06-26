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
        if ( EL_VERSION == 'v3' )
        {
            $r = Container::getInstance()->module('NewsletterSubscriber');
            $lists      = [];
            $recipients = [];

            foreach( $Newsletter->get('recipient') as $group )
            {
                $list = new Data('NewsletterGroup');
                $rst = $list->find( $group );

                if ( $rst )
                {
                    $lists[] = [
                        'id' => $group,
                        'name' => $list->get('name'),
                    ];

                    // on va chercher tous les destinataires
                    $rstRec = $r->getRepository()
                        ->getKit()
                        ->where_equal( $r->getEntity()->get('element_module_parent_id')->getColumn() , $group )
                        ->find_many();

                    if ( $rstRec )
                    {
                        foreach( $rstRec as $email )
                        {
                            $mail = trim( $email->get( $r->getEntity()->get('email')->getColumn() ) );
                            $mail = strtolower( $mail );
                            $mail = trim( $mail , "." );

                            $recipients[ $mail ]['email'] = $mail ;
                            $recipients[ $mail ]['lists'][] = $group ;
                        }
                    }
                }
            }

            $tab = [
                'lists' => $lists,
                'recipients' => $recipients,
            ];
        }
        else
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
                        if ( filter_var( $mail , FILTER_VALIDATE_EMAIL) )
                        {
                            $tab[ $mail ] = [
                                'Email' => $mail,
                                'lien_desinscription' => Http::getInstance()->getUrl() . "/newsletter/unsubscribe/" . $Newsletter->get('element_module_parent_id') . "/" . $mail
                            ] ;
                        }
                    }
                }
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

                    if ( filter_var( $mail , FILTER_VALIDATE_EMAIL) )
                    {
                        $tab[ $mail ] = [
                            'Email' => $mail,
                            'lien_desinscription' => Http::getInstance()->getUrl() . "/newsletter/unsubscribe/" . $Newsletter->get('element_module_parent_id') . "/" . $mail
                        ] ;
                    }
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
                    if ( filter_var( $mail , FILTER_VALIDATE_EMAIL) )
                    {
                        $tab[ $mail ] = [
                            'Email' => $mail,
                            'lien_desinscription' => Http::getInstance()->getUrl() . "/newsletter/unsubscribe/" . $Newsletter->get('element_module_parent_id') . "/" . $mail
                        ] ;
                    }
                }
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
            unset( $tab[ $email->get('mod_newslettercampaigngroupunsubscribe_email') ] ) ;
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

$app->get('/newsletter/unsubscribe/:id/:email[/{confirm}]', function ( $id , $email , $confirm = 0 ) use ( $app ) {
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
        $unsub = \DB::for_table('mod_newslettersubscriber')
            ->where("mod_newslettersubscriber_email", $email)
            ->find_one();

        $unsub->delete();

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
    elseif ( $confirm == 1 and $rst > 0 and $_GET["all"]=="on")
    {
        $subs = \DB::for_table('mod_newslettersubscriber')
            ->where("mod_newslettersubscriber_email", $email)
            ->find_many();
        foreach ($subs as $sub)
        {
            $sub->delete();
        }
    }

    return \App\Kernel\AppContext::twig()->render($res, 'Newsletter/unsubscribe.twig', [
        'newsletter_name' => $newsletter->get('name'),
        'idnl' => $id,
        'email' => $email,
        'unsub' => $unsub,
        'unsubscribe' => ( $rst == 0 ? false : true )
    ]);

})->name('newsletter_unsubscribe');
