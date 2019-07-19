<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class NewsletterSender extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setDefault();

        $this->build('name')
            ->column(1, 1)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_sender_sing_name') )
            ->name(Translate::getInstance()->getText('sender_name') );

        $this->build('email')
            ->column(1, 2)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_sender_email') )
            ->name(Translate::getInstance()->getText('sender_email') );

        $this->build('email_response')
            ->column(1, 2)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_response_email') )
            ->name(Translate::getInstance()->getText('response_email') );
    }
}