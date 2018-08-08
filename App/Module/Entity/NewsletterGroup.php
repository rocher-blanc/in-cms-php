<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterGroup extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'NewsletterSubscriber' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du groupe")
            ->name("Nom du groupe");
    }
}