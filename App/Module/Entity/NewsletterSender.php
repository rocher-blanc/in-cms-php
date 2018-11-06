<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
class NewsletterSender extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setDefault();

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner un nom d'expéditeur")
            ->name("Nom de l'expéditeur");

        $this->build('email')
            ->column(1, 2)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner l'adresse email de l'expéditeur")
            ->name("Email de l'expéditeur");

        $this->build('email_response')
            ->column(1, 2)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner l'adresse email de réponse")
            ->name("Email de réponse");
    }
}