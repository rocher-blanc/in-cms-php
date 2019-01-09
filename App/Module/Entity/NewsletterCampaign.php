<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterCampaign extends Builder
{
    protected function load()
    {
        $this->setModuleParent( 'NewsletterCampaignGroup' );
        $this->setFieldReference( 'subject' );

        $this->addAction( 'stats' );
        $this->addIcon( 'icon-bar-chart' , 'stats' , function($c) {
            return $c->statut == 3 ? true : false ;
        });

        $this->build('subject')
            ->column(1, 2)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le sujet")
            ->name("Sujet");

        $this->build('date')
            ->column(1, 2)
            ->isDate(true)
            ->notEmpty("Veuillez renseigner la date d'envoi")
            ->name("Date d'envoi");

        $this->build('sender')
            ->column(1, 3)
            ->isSelect()
            ->ManyToMany( 'NewsletterSender', "name" )
            ->notEmpty("Veuillez sélectionner l'expéditeur")
            ->name("Expéditeur");

        $this->build('recipient')
            ->column(1, 3)
            ->isCheckbox()
            ->ManyToMany( 'NewsletterGroup', "name" )
            ->notEmpty("Veuillez sélectionner les groupes de destinataires")
            ->name("Groupes de destinataires");

        $this->build('template')
            ->column(1, 3)
            ->isSelect()
            ->ManyToMany( 'NewsletterModel', "name" )
            ->notEmpty("Veuillez sélectionner le gabarit")
            ->name("Gabarit email");

        $this->build('statut')
            ->isHidden()
            ->style(function($c) {
                switch( $c->statut )
                {
                    case 1 : return "" ; break;
                    default : return 'text-center" style="margin: 0 auto; display: block; width: 180px;' ; break;
                }
            })
            ->updateValue(function($c) {
                switch( $c->stats['state'] )
                {
                    case 0 : $class = 'default'; break;
                    case 1 : $class = 'success'; break;
                    case 9 : $class = 'warning'; break;
                    case 10 : $class = 'success'; break;
                    case 11 : $class = 'danger'; break;
                }

                return '<span class="badge badge-'.$class.'" style="font-size: 14px;">' . $c->stats['state_str'] . '</span>';
            })
            ->name("Statut");

        $this->build('id_easyletter')
            ->isInteger()
            ->noFront()
            ->noBack()
            ->name("ID Easyletter");
    }
}