<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterModel extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleParent( 'NewsletterModelGroup' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du template")
            ->name("Nom");
    }
}