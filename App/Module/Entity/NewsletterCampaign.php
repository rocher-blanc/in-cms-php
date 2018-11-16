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
            ->column(1, 2)
            ->isSelect()
            ->ManyToMany( 'NewsletterSender', "name" )
            ->notEmpty("Veuillez sélectionner l'expéditeur")
            ->name("Expéditeur");

        $this->build('recipient')
            ->column(1, 2)
            ->isCheckbox()
            ->ManyToMany( 'NewsletterGroup', "name" )
            ->notEmpty("Veuillez sélectionner les groupes de destinataires")
            ->name("Groupes de destinataires");

        $this->build('template')
            ->column(1, 1)
            ->isSelect()
            ->ManyToMany( 'NewsletterModel', "name" )
            ->notEmpty("Veuillez sélectionner le gabarit")
            ->name("Gabarit email");

        $this->build('statut')
            ->column(1, 1)
            ->isSelect()
            ->option([
                1 => "En attente",
                2 => "Tranférée",
                3 => "Prête",
                4 => "Envoyée"
            ])
            ->notEmpty("Veuillez sélectionner un statut")
            ->name("Statut");
    }
}