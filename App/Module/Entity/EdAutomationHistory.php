<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Api\Easyletter;
use App\Kernel\Front\Data;
use App\Kernel\Front\Translate;

class EdAutomationHistory extends Builder
{
    protected function load()
    {
        $this->disableUpdate();
        $this->disableDelete();
        $this->disableCreate();

        $this->setFieldReference( 'date' );
        $this->addAction( 'stats' );
        $this->addIcon( 'icon-bar-chart' , 'stats' , function($c) {
            return $c->id_easyletter !== NULL && $c->stats['sent'] == 1 ? true : false ;
        });

        $this->addIcon( 'icon-reply' , 'resend' , function($c) {
            return $c->stats === NULL ? true : false ;
        });

        $this->build('email')
            ->column(1, 1)
            ->isVarchar("255", "email")
            ->notEmpty(Translate::getInstance()->getText( 'mandatory_email' ))
            ->name(Translate::getInstance()->getText( 'email' ));

        $this->build('date')
            ->isDate(true)
            ->name(Translate::getInstance()->getText( 'date' ));

        $this->build('information')
            ->noFront()
            ->noBack()
            ->isText("LONG")
            ->name("JSON - Informations");

        $this->build('automation')
            ->isSelect()
            ->ManyToMany("EdAutomation")
            ->name(Translate::getInstance()->getText( 'automation' ));

        $this->build('id_easyletter')
            ->isInteger()
            ->noFront()
            ->noBack()
            ->name("ID Easyletter");

        $this->build('error')
            ->isVarchar("500")
            ->noFront()
            ->noBack()
            ->name(Translate::getInstance()->getText( 'error_message' ));

        $this->build('statut')
            ->column(1, 1)
            ->isHidden()
            ->style(function($c) {
                switch( $c->statut )
                {
                    case 1 : return "" ; break;
                    default : return 'text-center" style="margin: 0 auto; display: block; width: 180px;' ; break;
                }
            })
            ->updateValue(function($c) {
                //dump( $c );
                $send = ( $c->stats['sent'] == 1 ? true : false );
                $delivery = ( $c->stats['sent'] == 1 && $c->stats['hard_bounces'] == 0 && $c->stats['soft_bounces'] == 0 ? true : false );
                $open = ( $c->stats['reads'] > 0 ? true : false );
                $clic = ( $c->stats['clicks'] > 0 ? true : false );
                $shield = false;
                $error = ( $c->stats['sent'] == 1 && ( $c->stats['hard_bounces'] > 0 || $c->stats['soft_bounces'] > 0 ) ? true : false );

                if ( $c->stats !== NULL && $error == true && $c->error == '' )
                {
                    $el = new Easyletter();
                    $stats = $el->stats( $c->id_easyletter );
                    $transco = $stats['state_mailing'] ;

                    $c->error = $transco[ $stats['destStats']['records'][0][3] ] ;

                    $data = new Data("EdAutomationHistory");
                    $data->find([
                        'id_easyletter' => $c->id_easyletter
                    ]);
                    $data->set('error' , $c->error );
                    $data->save();
                }

                if ( $send ) $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Envoyé" style="color: #007ca2;"></i>';
                else         $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Non envoyé" style="color: #ddd;"></i>';

                if ( $delivery ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Délivré" class="icon-check-sign" style="color: #449d44;"></i>' ;
                else             $str.= '<i data-toggle="tooltip" data-placement="top" title="Non délivré" class="icon-check-sign" style="color: #ddd;"></i>' ;

                if ( $open ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Ouvert" class="icon-email" style="color: darkorange;"></i>' ;
                else         $str.= '<i data-toggle="tooltip" data-placement="top" title="Jamais ouvert" class="icon-email" style="color: #ddd;"></i>' ;

                if ( $clic ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Cliqué" class="icon-link" style="color: darkcyan;"></i>' ;
                else         $str.= '<i data-toggle="tooltip" data-placement="top" title="Aucun clic" class="icon-link" style="color: #ddd;"></i>' ;

                if ( $shield ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Email bloqué : XXXXXXXXXXXXXXXX" class="icon-shield" style="color: darkred;"></i>' ;

                if ( $error ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Erreur : ' . $c->error . '" class="icon-warning-sign" style="color: darkred;"></i>' ;
                else          $str.= '<i data-toggle="tooltip" data-placement="top" title="Aucune erreur" class="icon-warning-sign" style="color: #ddd;"></i>' ;

                return $str ;
            })
            ->name(Translate::getInstance()->getText( 'status' ));
    }
}