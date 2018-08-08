<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterSubscriber extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'email' );
        $this->setModuleParent( 'NewsletterGroup' );

        $this->build('email')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner l'adresse email")
            ->name("Email");
    }
}