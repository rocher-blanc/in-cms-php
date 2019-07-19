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
            ->notEmpty(Translate::getInstance()->getText('mandatory_newsletter_name' ))
            ->name(Translate::getInstance()->getText('newsletter_name' ));
    }
}