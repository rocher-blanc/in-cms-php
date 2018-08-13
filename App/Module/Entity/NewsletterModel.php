<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterModel extends Builder
{
    protected function load()
    {
        $this->addIcon( 'icon-photo' , 'draw' );
        $this->setFieldReference( 'name' );
        $this->setModuleParent( 'NewsletterCampaignGroup' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le nom du template")
            ->name("Nom");
    }
}