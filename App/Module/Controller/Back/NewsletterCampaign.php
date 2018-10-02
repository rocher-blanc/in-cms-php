<?php

namespace App\Module\Controller\Back;

use App\Kernel\Back\Controller;
use App\Kernel\Back\Data;

class NewsletterCampaign extends Controller
{
    public function hookAddSaveAfter()
    {
        // j'envoi la rqt a api easyletter
    }

    protected function statsAction()
    {
        $data = new Data( $this->getEntityName() );
        $data->find([
            'id' => $this->getId()
        ]);

        $this->setRender('id' , $this->getId() );
        $this->setRender('rst' , [
            'subject' => $data->get('subject'),
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

        $this->setRender('links' , $tabLinks );

        $this->render('stats.twig');
    }
}