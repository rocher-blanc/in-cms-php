<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class NewsletterCampaignGroupUnsubscribe extends Builder
{
    protected function load()
    {
        $this->isDependency();
        $this->setFieldReference( 'email' );

        $this->build('email')
            ->isVarchar(255, "email")
            ->notEmpty(Translate::getInstance()->getText( mandatory_email_address))
            ->name(Translate::getInstance()->getText( email));
    }
}