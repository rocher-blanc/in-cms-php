<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class NewsletterCampaignGroup extends Builder
{
    protected function load()
    {
        $this->addDependency("NewsletterCampaignGroupUnsubscribe");
        $this->setModuleChild( 'NewsletterCampaign' );
        $this->setModuleChild( 'NewsletterModel' );
        $this->setFieldReference( 'name' );

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner un nom de newsletter")
            ->name(_('absence_msg'));

        Translate::getInstance()->getText( _('absence_msg') );
    }
}