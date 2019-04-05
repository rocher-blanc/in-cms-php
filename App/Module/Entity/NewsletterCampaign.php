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
            return $c->id_easyletter !== NULL && $c->stats['state'] == 10 ? true : false ;
        });

        $this->addIcon( 'icon-reply' , 'resend' , function($c) {
            return $c->stats === NULL ? true : false ;
        });

        $this->showEdit(function( $c ) {
            return false ;
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
            ->isHidden('INT',11)
            ->noSave()
            ->style(function($c) {
                switch( $c->statut )
                {
                    case 1 : return "" ; break;
                    default : return 'text-center" style="margin: 0 auto; display: block; width: 180px;' ; break;
                }
            })
            ->updateValue(function($c) {
                if ( is_array( $c->stats ) )
                {
                    switch( $c->stats['state'] )
                    {
                        case 0 : $class = 'info'; break; // pret
                        case 1 : $class = 'primary'; break; // en cours
                        case 9 : $class = 'warning'; break; // Suspendu
                        case 10 : $class = 'success'; break; // envoyée
                        case 11 : $class = 'danger'; break; // annulee
                        default : $class = 'default'; break; // en attente
                    }

                    switch( $c->stats['state'] )
                    {
                        case 0 :
                        case 9 :
                        case 10 :
                        case 11 : $txt = $c->stats['state_str']; break; // annulee
                        case 1 : $txt = $c->stats['state_str'] . ' (' . $c->stats['sent'] . "/" . $c->stats['to_send'] . ")"; break; // en cours
                        default : $txt = 'En attente'; break; // en attente
                    }

                    return '<span class="badge badge-'.$class.'" style="font-size: 14px;">' . $txt . '</span>';
                }
                else
                {
                    $date = (new \DateTime($c->date_created))->format('U') + 120;
                    if ( time() > $date )
                    {
                        return '<span class="badge badge-danger" style="font-size: 14px;">Erreur</span>';
                    }
                    else
                    {
                        return '<span class="badge badge-info" style="font-size: 14px;">En attente</span>';
                    }
                }
            })
            ->name("Statut");

        $this->build('id_easyletter')
            ->isInteger()
            ->noFront()
            ->noBack()
            ->name("ID Easyletter");
    }
}