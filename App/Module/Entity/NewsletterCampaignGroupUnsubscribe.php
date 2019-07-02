<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterCampaignGroupUnsubscribe extends Builder
{
    protected function load()
    {
        $this->isDependency();
        $this->setFieldReference( 'email' );

        $this->build('email')
            ->isVarchar(255, "email")
            ->notEmpty("Veuillez renseigner l'adresse email")
            ->name("E-mail");
    }
}