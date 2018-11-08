<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;
use App\Api\Easyletter;

class EdAutomationHistory extends Controller
{
    protected function statsAction()
    {
        $el = new Easyletter();
        
        $data = new Data( $this->getEntityName() );
        $data->find([
            'id' => $this->getId()
        ]);

        $this->setRender('id' , $this->getId() );
        $this->setRender('rst' , [
            'subject' => "sujet de l automation",
            'destinataires' => [
                "Groupe 1",
                "Groupe 2",
                "Groupe 3",
            ],
        ] );

        $tclic = 0;
        for( $i = 1; $i <= 7; $i++ )
        {
            $clic = rand(0,10) ;
            $tclic += $clic ;
            $tabLinks[] = [
                "clicCount" => $clic,
                "link" => "https://www.domaine.fr/lien-$i.html",
                "recipientsClic" => rand(0,8)
            ];
        }

        $this->setRender('res' , [
            'ToSend' => 234,
            "Sent" => 234,
            "HardBounces" => 2,
            "RecipientsRead" => 35,
            "RecipientsClic" => $tclic,
            "SoftBounces" => 5,
            "Unsubscribe" => 1,
            "Sent" => 234,
            "State" => 1,
            "StateStr" => "Routage terminé",
        ] );

        $tabStatus = [
            "0" => "Mail délivré",
            "2" => "Nom de domaine inconnu",
            "3" => "Adresse email inconnu",
            "4" => "Relayage interdit",
            "6" => "Boîte aux lettres pleines",
            "7" => "Mail non délivré (refusé par le serveur distant)",
            "8" => "Mail bloqué par l'antispam",
            "9" => "Mail illisible (problème d'encodage des caractères)",
            "10" => "Boîte aux lettres non disponible",
            "11" => "Erreur temporaire",
            "12" => "Boîte aux lettres inactives (inutilisée)",
            "13" => "Destinataire absent (en congé ou autre)",
        ] ;

        for( $i = 1; $i <= 234; $i++ )
        {
            if( $i % 2 == 0 or $i == 1)
            {
                $state = 0;
            }
            else
            {
                $state = rand(0,13);
            }

            $tabDests[] = [
                "recipientId" => $i,
                "email" => "email-$i@domain.com",
                "MobilePhone" => null,
                "state" => ( array_key_exists($state, $tabStatus) ? $state : 0 ),
                "read" => rand(0,1),
                "readDateUTC" => "2018-01-" . rand(1,31) . " " . rand(10,23) . ":" . rand(10,59) . ":00",
                "unsubscribe" => "0",
                "unsubscribeDateUTC" => null,
                "vacation" => null,
                "clicCount" => rand(0,5),
                "linkClicCount" => 0
            ];
        }

        $this->setRender('status' , $tabStatus);
        $this->setRender('dests' , $tabDests );
        $this->setRender('links' , $tabLinks );

        $this->render('stats.twig');
    }
}