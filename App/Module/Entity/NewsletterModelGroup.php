<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterModelGroup extends Builder
{
    protected function load()
    {
        $this->setModuleChild( 'NewsletterModel' );
        $this->setFieldReference( 'name' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du template")
            ->name("Nom");
    }
}