<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class EdAutomationHistory extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'date' );
        $this->addAction( 'stats' );
        $this->addIcon( 'icon-bar-chart' , 'stats' , function($c) {
            return $c->id_easyletter !== NULL ? true : false ;
        });

        $this->build('email')
            ->column(1, 1)
            ->isVarchar("255", "email")
            ->notEmpty("Veuillez renseigner un email")
            ->name("Email");

        $this->build('date')
            ->isDate(true)
            ->name("Date");

        $this->build('information')
            ->noFront()
            ->noBack()
            ->isText()
            ->name("JSON - Informations");

        $this->build('automation')
            ->isSelect()
            ->ManyToMany("EdAutomation")
            ->name("Automation");

        $this->build('id_easyletter')
            ->isInteger()
            ->noFront()
            ->noBack()
            ->name("ID Easyletter");

        $this->build('statut')
            ->column(1, 1)
            ->isInteger()
            ->style(function($c) {
                switch( $c->statut )
                {
                    case 1 : return "" ; break;
                    default : return 'text-center" style="margin: 0 auto; display: block; width: 180px;' ; break;
                }
            })
            ->updateValue(function($c) {
                $send = true ;
                $delivery = false;
                $open = false;
                $clic = false;
                $shield = true;
                $error = false;

                if ( $send ) $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Envoyé" style="color: #007ca2;"></i>';
                else         $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Non envoyé" style="color: #ddd;"></i>';

                if ( $delivery ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Délivré" class="icon-check-sign" style="color: #449d44;"></i>' ;
                else             $str.= '<i data-toggle="tooltip" data-placement="top" title="Non délivré" class="icon-check-sign" style="color: #ddd;"></i>' ;

                if ( $open ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Ouvert le 00/00/0000 à 00:00" class="icon-email" style="color: darkorange;"></i>' ;
                else         $str.= '<i data-toggle="tooltip" data-placement="top" title="Jamais ouvert" class="icon-email" style="color: #ddd;"></i>' ;

                if ( $clic ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Cliqué le 00/00/0000 à 00:00" class="icon-link" style="color: darkcyan;"></i>' ;
                else         $str.= '<i data-toggle="tooltip" data-placement="top" title="Aucun clic" class="icon-link" style="color: #ddd;"></i>' ;

                if ( $shield ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Email bloqué : XXXXXXXXXXXXXXXX" class="icon-shield" style="color: darkred;"></i>' ;

                if ( $error ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Erreur : XXXXXXXXXXXXXXXX" class="icon-warning-sign" style="color: darkred;"></i>' ;

                return $str ;
            })
            ->name("Statut");
    }
}