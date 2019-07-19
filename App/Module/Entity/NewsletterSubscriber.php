<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class NewsletterSubscriber extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'email' );
        $this->setModuleParent( 'NewsletterGroup' );

        $this->build('email')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_email_address' ))
            ->name(Translate::getInstance()->getText('email') );
    }
}