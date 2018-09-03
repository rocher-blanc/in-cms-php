<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

class NewsletterCampaignGroup extends Builder
{
    protected function load()
    {
        $this->setModuleChild( 'NewsletterCampaign' );
        $this->setModuleChild( 'NewsletterModel' );
        $this->setFieldReference( 'name' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner un nom de newsletter")
            ->name("Nom de la newsletter");
    }
}